<?php

namespace App\Services;

use ZKLibrary; // from nurkarim/zkteco-sdk-php
use App\Models\DeviceFingerprint;

/**
 * Push fingerprint templates to ZKTeco devices using ZKLibrary (UDP 4370).
 *
 * We ONLY use this for writing templates. Reading can stay on rats/zkteco if you like.
 */
class ZkLibraryTemplateService
{
    protected string $ip;
    protected int $port;

    public function __construct(string $ip, int $port = 4370)
    {
        $this->ip   = $ip;
        $this->port = $port;
    }

    /**
     * Push ONE DeviceFingerprint row to the device as a NEW template.
     *
     * - Ensures the user exists on the device (setUser).
     * - Builds the TTemplate binary struct and calls setUserTemplate().
     *
     * Returns true on success, false on any error.
     */
    public function pushDeviceFingerprint(DeviceFingerprint $df): bool
    {
        // Safety checks
        if (! $this->ip) {
            return false;
        }

        // Instantiate ZKLibrary client
        $zk = new ZKLibrary($this->ip, $this->port);

        try {
            if (! $zk->connect()) {
                return false;
            }

            // Disable device while writing
            $zk->disableDevice();

            // 1) Ensure user exists on device
            //    ZKLibrary::setUser($uid, $userid, $name, $password, $role)
            //    We'll use device_uid as the internal UID, user_code as the visible ID.
            $uid      = (int) $df->device_uid;             // internal short (2 bytes)
            $userid   = (string) $df->user_code;           // "47", etc.
            $name     = $df->user_name ?: $userid;
            $password = '';
            $role     = 0; // LEVEL_USER

            $zk->setUser($uid, $userid, $name, $password, $role);

            // 2) Decode template from base64
            $binaryTemplate = base64_decode($df->template_base64, true);
            if ($binaryTemplate === false) {
                // If somehow not valid base64, assume it's raw binary
                $binaryTemplate = $df->template_base64;
            }

            if ($binaryTemplate === '' || $binaryTemplate === null) {
                // Nothing to push
                $zk->enableDevice();
                $zk->disconnect();
                return false;
            }

            // 3) Build the TTemplate binary struct for setUserTemplate()
            //
            // typedef struct _Template_{
            //     U16 Size;       // length of fingerprint template
            //     U16 PIN;        // corresponds with user PIN (UID)
            //     char FingerID;  // finger index (0-9)
            //     char Valid;     // 1 = valid
            //     char *Template; // actual template bytes
            // } TTemplate;
            //
            // So the binary layout is:
            // [2 bytes size][2 bytes pin][1 byte fingerId][1 byte valid][template bytes]
            //
            $size        = strlen($binaryTemplate);
            $pin         = $uid; // use UID as PIN (matches setUser)
            $fingerIndex = (int) ($df->finger_index ?? 0);
            $valid       = 1;

            $templateStruct =
                pack('v', $size) .          // U16 Size
                pack('v', $pin) .           // U16 PIN
                chr($fingerIndex) .         // char FingerID
                chr($valid) .               // char Valid
                $binaryTemplate;            // raw Template bytes

            // 4) Upload template
            $result = $zk->setUserTemplate($templateStruct);

            // Re-enable and disconnect
            $zk->enableDevice();
            $zk->disconnect();

            // setUserTemplate usually returns true/false. Treat non-empty as success.
            return (bool) $result;
        } catch (\Throwable $e) {
            // Try to clean up connection, but ignore errors
            try {
                $zk->enableDevice();
                $zk->disconnect();
            } catch (\Throwable $ignore) {
                // ignore
            }

            // Optional: log error so you can see what's happening
            \Log::error('ZkLibraryTemplateService push failed', [
                'device_ip' => $this->ip,
                'error'     => $e->getMessage(),
            ]);

            return false;
        }
    }
}
