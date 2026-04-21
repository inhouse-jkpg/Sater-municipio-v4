<?php

namespace ModularityNoticeboard\Module;

class Noticeboard extends \Modularity\Module {
    public $slug = 'noticeboard';

    private const settings = array(
        'id' => 'noticeboard',
        'nameSingular' => 'Noticeboard',
        'namePlural' => 'Noticeboards',
        'description' => 'List announcements',
        'supports' => array(),
        'icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDE5MiAxOTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDE5MiAxOTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGQ9Ik0xMS4wMSw2LjMxN3YyOC41OTdoMTY5Ljk4VjYuMzE3YzAtMy40ODgtMi44MjktNi4zMTctNi4zMTktNi4zMTdIMTcuMzI5QzEzLjgzOSwwLDExLjAxLDIuODI5LDExLjAxLDYuMzE3eiBNMTQxLjU2Nyw4LjUNCgljMy44NjYsMCw3LDMuMTM0LDcsN3MtMy4xMzQsNy03LDdzLTctMy4xMzQtNy03UzEzNy43MDEsOC41LDE0MS41NjcsOC41eiBNNTAuNDMzLDguNWMzLjg2NiwwLDcsMy4xMzQsNyw3cy0zLjEzNCw3LTcsNw0KCXMtNy0zLjEzNC03LTdTNDYuNTY2LDguNSw1MC40MzMsOC41eiBNMy40MjYsNTYuMjMxbDcuNTgzLDUwLjE5NHYxLjI2NHY0MC4wNzl2MzcuOTE0YzAsMy40ODgsMi44MjksNi4zMTcsNi4zMTksNi4zMTdoMTU3LjM0Mw0KCWMzLjQ5LDAsNi4zMTktMi44MjksNi4zMTktNi4zMTd2LTM3LjkxNHYtNDAuMDc5di0xLjI2NGw3LjU4My01MC4xOTRjMC0zLjQ4OC0yLjgzLTYuMzE3LTYuMzE5LTYuMzE3SDkuNzQ2DQoJQzYuMjU2LDQ5LjkxNCwzLjQyNiw1Mi43NDMsMy40MjYsNTYuMjMxeiBNMTExLjk3NCw4My41NjhsMjcuNjYxLTIuOXY3OS4zMzFIMTI5VjkxLjU3MWwtMTcuMDI2LDAuMTYxVjgzLjU2OHogTTQ2LjAxNywxMzkuNjk2DQoJbDAuMTA3LTAuMzIyaDEwLjA5OGMwLDMuNzk2LDEuMzcsNi45OTEsNC4xMDksOS41ODhjMi43MzksMi41OTYsNi4yNzUsMy44OTQsMTAuNjA4LDMuODk0YzQuMjk3LDAsNy42ODEtMS4zMzQsMTAuMTUxLTQuMDAxDQoJYzIuNDcxLTIuNjY4LDMuNzA2LTYuMTUsMy43MDYtMTAuNDQ3YzAtNC45MDUtMS4xMzctOC41NzUtMy40MTEtMTEuMDExYy0yLjI3NC0yLjQzNS01Ljg0Ni0zLjY1Mi0xMC43MTUtMy42NTJoLTguNzAxdi04LjIxOGg4LjcwMQ0KCWM0LjcyNywwLDguMDY1LTEuMTczLDEwLjAxNy0zLjUxOGMxLjk1MS0yLjM0NiwyLjkyNy01LjYzMSwyLjkyNy05Ljg1NmMwLTMuOTM4LTEuMDY2LTcuMTE2LTMuMTk2LTkuNTM0DQoJYy0yLjEzMS0yLjQxNi01LjI5MS0zLjYyNS05LjQ4LTMuNjI1Yy00LjA4MiwwLTcuNDU3LDEuMjYyLTEwLjEyNCwzLjc4N2MtMi42NjgsMi41MjMtNC4wMDIsNS42ODUtNC4wMDIsOS40OEg0Ni43MTVsLTAuMTYxLTAuMzIyDQoJYy0wLjE4LTUuODcyLDIuMDQxLTEwLjg4NSw2LjY2LTE1LjAzOWM0LjYxOS00LjE1MywxMC41MjctNi4yMywxNy43MjUtNi4yM2M3LjI2OSwwLDEyLjk2MiwxLjgzNSwxNy4wOCw1LjUwNQ0KCWM0LjExNywzLjY3MSw2LjE3Nyw5LjA2OSw2LjE3NywxNi4xOTRjMCwzLjY1Mi0wLjk1OCw2Ljk4Mi0yLjg3Myw5Ljk5Yy0xLjkxNiwzLjAwOC00LjU5Miw1LjM1NC04LjAzLDcuMDM2DQoJYzMuOTM4LDEuNTc2LDYuOTM3LDMuOTY2LDguOTk3LDcuMTdjMi4wNTksMy4yMDUsMy4wODgsNy4wODIsMy4wODgsMTEuNjI5YzAsNy4xNjItMi4yNjUsMTIuNzc0LTYuNzk1LDE2LjgzOA0KCWMtNC41Myw0LjA2NC0xMC40MTIsNi4wOTctMTcuNjQ0LDYuMDk3Yy02LjY2LDAtMTIuNTI0LTEuOTYtMTcuNTktNS44ODJDNDguMjgxLDE1MS4zMjUsNDUuODM3LDE0Ni4xNDIsNDYuMDE3LDEzOS42OTZ6Ii8+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg=='
    );

    public function init() {
        $this->ID = self::settings['id'];
        $this->nameSingular = self::settings['nameSingular'];
        $this->namePlural = self::settings['namePlural'];
        $this->description = self::settings['description'];
        $this->supports = self::settings['supports'];
        $this->icon = self::settings['icon'];

        add_filter('/Modularity/externalViewPath', function ($arr) {
            $arr['mod-' . self::settings['id']] = MODULARITYNOTICEBOARD_PATH . 'source/php/Module/views/';
            return $arr;
        }, 10, 1);
    }

    /**
     * Data array
     * @return array $data
     */
    public function data(): array {
        $data = array();

        $data = array_merge($data, (array) \Modularity\Helper\FormatObject::camelCase(
            get_fields($this->ID)
        ));

        $posts = $this->get_posts($data['filterPostsByTag']);

        $data = array_merge($data, [
            'classes' => implode(' ', apply_filters('Modularity/Module/Classes', ['box', 'box-panel'], $this->post_type, $this->args)),
            'postTitle' => $postTitle ?? '',
            'introductoryText' => isset($introductoryText) ? wpautop($introductoryText) : '',
            'postItems' => $posts,
            'strings' => $this->get_strings(),
        ]);

        return $data;
    }

    private function get_strings() {
        return [
            'posted' => __('Posted', 'modularity-noticeboard'),
            'taken_down' => __('To be taken down', 'modularity-noticeboard'),
            'no_posts' => __('No posts available.', 'modularity-noticeboard'),
        ];
    }

    private function get_posts($tags = []) {
        $getPostsArgs = [
            'post_type' => MODULARITYNOTICEBOARD_POST_TYPE,
            'posts_per_page' => -1,
            'orderby' => 'order_clause post_date',
            'order' => 'desc',
            'tax_query' => [[
                'taxonomy' => MODULARITYNOTICEBOARD_TAXONOMY,
                'field' => 'term_id',
                'terms' => intval($tags),
            ]],
        ];

        $posts = get_posts($getPostsArgs);
        $postItems = [];

        foreach ($posts as $post) {
            $title = apply_filters('the_title', $post->post_title);
            $published = get_the_date('Y-m-d', $post);
            $custom = get_post_custom($post->ID);

            $unpubDate = get_post_meta($post->ID, 'unpublish-date', true);
            if (!empty($custom['unpublish-date'][0])) {
                $unpubDate = unserialize($custom['unpublish-date'][0]);
                $unpubDate = sprintf("%s-%s-%s", $unpubDate['aa'], $unpubDate['mm'], $unpubDate['jj']);

                // Unpublish post if need be
                if (strtotime($unpubDate) <= time()) {
                    $postStatus = get_post_meta($post->ID, 'unpublish-action', true);

                    wp_update_post([
                        'ID'          => $post->ID,
                        'post_status' => $postStatus,
                    ]);
                    
                    delete_post_meta($post->ID, 'unpublish-action');
                    delete_post_meta($post->ID, 'unpublish-mm');
                    delete_post_meta($post->ID, 'unpublish-jj');
                    delete_post_meta($post->ID, 'unpublish-aa');
                    delete_post_meta($post->ID, 'unpublish-hh');
                    delete_post_meta($post->ID, 'unpublish-mn');
                    
                    continue;
                }
            }

            $meetingDate = '';
            if (!empty($custom['meeting_date'][0])) {
                $meetingDate = date('Y-m-d', strtotime($custom['meeting_date'][0]));
            }

            $link = isset($custom['link'][0]) ? $custom['link'][0] : false;

            $postItems[] = [
                'title' => $title,
                'published' => $published,
                'unpubDate' => $unpubDate,
                'meetingDate' => $meetingDate,
                'link' => $link,
            ];

            return $postItems;
        }
    }
}
