<?php

namespace Visit;

class Acf
{
    public function __construct()
    {
        // Acf auto import and export ACF Fields
        add_action('acf/init', function () {
            $acfExportManager = new \AcfExportManager\AcfExportManager();
            $acfExportManager->setTextdomain('visit');
            $acfExportManager->setExportFolder(plugin_dir_path(__FILE__) . 'AcfFields/');
            $acfExportManager->autoExport([
                'visit-visitorinformation'       => 'group_63f8b99f12d0f',
                'visit-activity'                 => 'group_63dcbd004f856',
                'visit-cuisine'                  => 'group_63dbb0ca3dab5',
                'visit-other'                    => 'group_63dd0967db81c',
                'visit-weather'                  => 'group_641c187122f99',
                'visit-quicklinks-colors'        => 'group_641d735c44589',
                'visit-infopoints'               => 'group_643e86aea2296',
                'visit-term-description-wysiwyg' => 'group_643ff760cff16',
            ]);
            $acfExportManager->import();
        });
    }
}
