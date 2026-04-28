<?php

declare(strict_types=1);

namespace Mediaflow;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class PingUsageController
{
    public function __invoke(WP_REST_Request $request)
    {
        // Validate the request parameters.
        if (
            !$request->has_param('post_id') ||
            !$request->has_param('mediaflow_id') ||
            !$request->has_param('user')
        ) {
            return new WP_Error('invalid_params', 'Invalid parameters', [
                'status' => 400,
            ]);
        }

        // Fetch an access token from Mediaflow API.
        $token = mediaflow_get_access_token();

        if (!$token) {
            return new WP_Error('invalid_credentials', 'Unauthorized', [
                'status' => 401,
            ]);
        }

        // If the file is removed or added, defaults to false.
        $removed = filter_var(
            $request->get_param('removed'),
            FILTER_VALIDATE_BOOLEAN
        );

        $postId = intval($request->get_param('post_id'));

        // Register the usage of the file to Mediaflow.
        // https://static.mediaflowpro.com/doc/#hdr25
        $response = wp_remote_post(
            'https://customerapi.mediaflowpro.com/1/files/multiple/usages',
            [
                'body' => json_encode([
                    'amount' => 1,
                    'date' => wp_date('Y-m-d', time()),
                    'id' => [$request->get_param('mediaflow_id')],
                    'project' => 'WordPress',
                    'contact' => $request->get_param('user'),
                    'removed' => $removed,
                    'types' => ['web'],
                    'web' => [
                        'page' => get_the_permalink($postId),
                        'pageName' => get_the_title($postId),
                    ],
                ]),
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        $body = json_decode($response['body'], true);

        return new WP_REST_Response($body, $response['response']['code']);
    }
}
