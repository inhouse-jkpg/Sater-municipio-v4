<?php

$fields = json_decode(json_encode(get_fields($module->ID)));

$getPostsArgs = array(
    'post_type' => MODULARITYNOTICEBOARD_POST_TYPE,
    'posts_per_page' => -1,
    'meta_key'      => 'announcement_type',
    'meta_value'    => $fields->filter_posts_by_tag,
    'orderby' => 'order_clause post_date',
    'order' => 'desc',
);

$posts = get_posts($getPostsArgs);
?>


<div class="<?php echo implode(' ', apply_filters('Modularity/Module/Classes', array('box', 'box-panel'), $module->post_type, $args)); ?>">
    <h4 class="box-title"><?php echo $module->post_title; ?></h4>
    <?php if (!empty($module->introductory_text)): ?>
        <?php echo wpautop($module->introductory_text) ?>
    <?php endif ?>
    <ul>
        <?php
        if (count($posts) > 0) :
        foreach ($posts as $post) :
            $title = apply_filters('the_title', $post->post_title);
            $published = get_the_date('Y-m-d', $post);
            $custom = get_post_custom($post->ID);
            $unpubDate = get_post_meta($post->ID, 'unpublish-date', true);
            if (!empty($custom['unpublish-date'][0])) {
                $unpubDate = unserialize($custom['unpublish-date'][0]);
                $unpubDate = sprintf("%s-%s-%s", $unpubDate['aa'], $unpubDate['mm'], $unpubDate['jj']);
            }
            $meetingDate = '';
            if (!empty($custom['meeting_date'][0])) {
                $meetingDate = date('Y-m-d', strtotime($custom['meeting_date'][0]));
            }
            $link = isset($custom['link'][0]) ? $custom['link'][0] : false;
        ?>
            <li>
                <?php if ($link): ?>
                    <a href="<?php echo $link ?>"><?php echo $title; ?> <?php echo $meetingDate; ?></a>
                <?php else: ?>
                    <?php echo $title; ?> <?php echo $meetingDate; ?>
                <?php endif ?>
                Anslaget: <?php echo $published ?>
                <?php if ($unpubDate): ?>Tas ner: <?php echo $unpubDate; ?><?php endif ?>
            </li>
        <?php endforeach; endif; ?>
    </ul>
</div>
