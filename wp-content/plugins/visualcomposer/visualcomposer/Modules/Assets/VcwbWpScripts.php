<?php

namespace VisualComposer\Modules\Assets;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use WP_Scripts;

class VcwbWpScripts extends WP_Scripts
{
    /**
     * @codingStandardsIgnoreStart
     *
     * @param bool|array $handles
     * @param bool $group
     *
     * @return array
     */
    public function do_items($handles = false, $group = false)
    {
        /*
         * If nothing is passed, print the queue. If a string is passed,
         * print that item. If an array is passed, print those items.
         */
        $handles = false === $handles ? $this->queue : (array)$handles;
        $this->all_deps($handles);

        foreach ($this->to_do as $key => $handle) {
            if (!in_array($handle, $this->done, true) && isset($this->registered[ $handle ])) {
                /*
                 * Attempt to process the item. If successful,
                 * add the handle to the done array.
                 *
                 * Unset the item from the to_do array.
                 */
                $result = $this->do_item($handle, $group);
                if ($result) {
                    $this->done[] = $handle;
                }
                if (!$result && strpos($handle, 'vcv:') === 0) {
                    continue; // keep script in to_do if it is vcv:
                }
                unset($this->to_do[ $key ]);
            }
        }

        return $this->done;
    }

    public function do_item($handle, $group = false)
    {
        if ($group !== 1 && strpos($handle, 'vcv:') === 0) {
            // it starts from vcv:
            return false;
        }

        return parent::do_item($handle, $group);
    }
}
