<?php

declare(strict_types=1);

namespace Mediaflow;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class UploadFilesController
{
    public function __invoke(WP_REST_Request $request)
    {
        $body = $request->get_json_params();

        // Download the image as temporary file.
        $url = download_url($body['url']);

        if ($url instanceof WP_Error) {
            return $url;
        }

        // Move the image into the uploads directory.
        $file = [
            'name' => basename($body['filename']),
            'type' => mime_content_type($url),
            'tmp_name' => $url,
            'size' => filesize($url),
        ];

        $sideload = wp_handle_sideload($file, [
            'test_form' => false,
        ]);

        if (!empty($sideload['error'])) {
            return new WP_Error('mediaflow_sideload', $sideload['error'], [
                'status' => 400,
            ]);
        }

        // Insert the image into WordPress.
        $attachment = [
            'guid' => $sideload['url'],
            'post_mime_type' => $sideload['type'],
            'post_title' => basename($sideload['file']),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $id = wp_insert_attachment($attachment, $sideload['file'], 0, true);

        if ($id instanceof WP_Error) {
            return $id;
        }

        // Add Mediaflow ID to the attachment meta data.
        $data = wp_generate_attachment_metadata($id, $sideload['file']);

        $data['mediaflow_id'] = $body['id'];

        wp_update_attachment_metadata($id, $data);

        // Add the alternative text to the image.
        if (!empty($body['altText'])) {
            update_post_meta($id, '_wp_attachment_image_alt', $body['altText']);
        }

        // Return the attachment ID.
        return new WP_REST_Response($id, 201);
    }
}
