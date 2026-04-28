<?php

namespace ModularityNoticeboard\Admin\Roles;

class General
{
    public function __construct()
    {
    }

    /**
     * Add role
     */
    public function addRole()
    {
            $editor_role = get_role('editor');
            $capabilities = $editor_role->capabilities;
            $capabilities += array(
                'read_mod_noticeboard_announcement' => true,
                'read_private_mod_noticeboard_announcements' => true,
                'edit_mod_noticeboard_announcement' => true,
                'edit_mod_noticeboard_announcements' => true,
                'edit_others_mod_noticeboard_announcements' => true,
                'edit_published_mod_noticeboard_announcements' => true,
                'publish_mod_noticeboard_announcements' => true,
                'delete_mod_noticeboard_announcements' => true,
                'delete_others_mod_noticeboard_announcements' => true,
                'delete_private_mod_noticeboard_announcements' => true,
                'delete_published_mod_noticeboard_announcements' => true,
            );
            add_role(
                'noticeboard-admin',
                'Noticeboard admin',
                $capabilities
            );

            //delete_option('_author_role_bkp');
        //}
    }
}
