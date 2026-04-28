<?php

namespace ModularityDynamicGuides\Module;

/**
 * Class DynamicGuides
 * @package ModularityDynamicGuides\Module
 */
class DynamicGuides extends \Modularity\Module
{
    public $slug = 'dynamic-guide';
    public $supports = array();
    public $cacheTtl = 0;
    
    /**
     * Initialize the module
     */
    public function init()
    {
        $this->nameSingular         = __("Dynamic guide", 'modularity-dynamic-guides');
        $this->namePlural           = __("Dynamic guides", 'modularity-dynamic-guides');
        $this->description          = __("Creates dynamic guides.", 'modularity-dynamic-guides');
        $this->isBlockCompatible    = false;
    }
    
    /**
     * View data
     * @return array
    */
    public function data(): array
    {
        $data                    = [];
        $fields                  = get_fields($this->ID);
        $data['startPage']       = $this->getStartPageValues($fields);
        $data['endPage']         = $this->getEndPageValues($fields);
        $data['resultsPage']     = $this->getResultsPageValues($fields);
        $data['steps']           = $this->getChoicesSteps($fields);
        $data['backgroundImage'] = !empty($fields['dynamic_guide_background_image']) ?
        $this->getImageFromId($fields['dynamic_guide_background_image']) : false;
        $data['outcome']         = $this->getOutcome($fields);
        $data['lang']            = $this->getTranslatedViewStrings();

        return $data;
    }

    /**
     * Return translated strings as an array
     * @return array
     */
    private function getTranslatedViewStrings(): array
    {
        return [
            'previousStep' => __('Previous step', 'modularity-dynamic-guides'),
        ];
    }

    /**
     * Get outcome data
     * @param array $fields
     * @return array|false
     */
    private function getOutcome(array $fields)
    {
        if (!isset($_GET['outcome'])) { return false; }
        $outcomes     = $fields['dynamic_guide_outcomes'];
        $outcomeIndex = $this->getOutcomeIndex($fields);

        if ($outcomeIndex === false || empty($outcomes[$outcomeIndex])) {
            return false;
        }

        $outcome = array_merge($this->defaultOutcomeValues(), $outcomes[$outcomeIndex]);

        if ($outcome['outcome_posts']) {
            $outcome['outcome_posts'] = $this->getPosts($outcome['outcome_posts']);
        }

        if ($outcome['outcome_image']) {
            $outcome['outcome_image'] = $this->getImageFromId($outcome['outcome_image']);
        }
        
        return \Municipio\Helper\FormatObject::camelCase($outcome);
    }


    /**
     * Get default outcome values
     * @return array
     */
    private function defaultOutcomeValues(): array
    {
        return [
            'outcome_posts'                 => false,
            'outcome_content'               => false,
            'outcome_title'                 => false,
            'outcome_image'                 => false,
            'outcome_call_to_action_url'    => false,
            'outcome_call_to_action_label'  => false
        ];
    }

    /**
     * Get outcome index based on URL parameters
     * @param array $fields
     * @return false|int
     */
    private function getOutcomeIndex(array $fields) {
        $outcomes = $fields['dynamic_guide_outcomes_hidden'];
        $urlOutcome = stripslashes($_GET['outcome']);
        $urlOutcome = (array) json_decode($urlOutcome, false);

        
        
        if (!empty($urlOutcome) && !empty($outcomes) && is_string($outcomes)) {
            $outcomes = (array) json_decode($outcomes);
            $matchingOutcome = false;
            foreach ($outcomes as $index => $outcome) {
                $outcome = (array) $outcome;
                $arrayKeysAreTheSame = $this->checkSameArrayKeys($outcome, $urlOutcome);
                
                if ($arrayKeysAreTheSame) {
                    $matchingOutcome = $index;
                    break;
                }
            }
            
            return $matchingOutcome;
        }

        return false;
    }

    /**
     * Check if array keys match
     * @param array $outcome
     * @param array $urlOutcome
     * @return bool
     */
    private function checkSameArrayKeys(array $outcome, array $urlOutcome)
    {
        ksort($outcome);
        ksort($urlOutcome);
        if (array_keys($urlOutcome) !==  array_keys($outcome)) {
            return false;
        }
        
        $matchingValues = true;
        foreach ($urlOutcome as $key => $value) {
            if ($value !== $outcome[$key]) {
                $matchingValues = false;
                break;
            }
        }

        return $matchingValues;
    }


    /**
     * Get posts based on post IDs
     * @param array $postIds
     * @return array
     */
    private function getPosts(array $postIds) 
    {
        $posts = [];
        foreach ($postIds as $postId) {
            $post = get_post($postId);
            if (!empty($post)) {
                $post = \Municipio\Helper\Post::preparePostObject($post);
                $posts[] = $post;
            }
        }

        return $posts;
    }

    /**
     * Get start page values
     * @param array $fields
     * @return array
     */
    private function getStartPageValues(array $fields): array
    {
        $startPage = !empty($fields['dynamic_guide_start_page']) ?
        array_merge($this->defaultStartPageValues(), $fields['dynamic_guide_start_page']) :
        [];

        return $startPage;
    }

    /**
     * Get end page values
     * @param array $fields
     * @return array
     */
    private function getEndPageValues(array $fields) {
        $endPage = !empty($fields['dynamic_guide_end_page']) ? 
        array_merge($this->defaultEndPageValues(), $fields['dynamic_guide_end_page']) : 
        [];

        return $endPage;
    }
    
    /**
     * Get results page values
     * @param array $fields
     * @return array
     */
    private function getResultsPageValues(array $fields) {
        $resultsPage = !empty($fields['dynamic_guide_results_page']) ? 
        array_merge($this->defaultResultsPageValues(), $fields['dynamic_guide_results_page']) : 
        [];

        return $resultsPage;
    }

    /**
     * Get choices steps
     * @param array $fields
     * @return false|array
     */
    private function getChoicesSteps(array $fields) 
    {
        return !empty($fields['dynamic_guide_steps']) ? $fields['dynamic_guide_steps'] : false;
    }

    /**
     * Get default start page values
     * @return array
     */
    private function defaultStartPageValues(): array
    {
        return [
            'heading' => '',
            'preamble' => '',
            'button_label' => '',
        ];
    }
    
    /**
     * Get default start page values
     * @return array
     */
    private function defaultEndPageValues(): array
    {
        return [
            'heading' => '',
            'result_button_label' => '',
            'restart_button_label' => '',
        ];
    }
    
    /**
     * Get default start page values
     * @return array
     */
    private function defaultResultsPageValues(): array
    {
        return [
            'restart_button_label' => '',
        ];
    }

    /**
     * Get image data based on attachment ID
     * @param $id
     * @return false|array
     */
    private function getImageFromId($id) 
    {
        if ($id && class_exists('\Municipio\Helper\Image')) {
            return \Municipio\Helper\Image::getImageAttachmentData($id, [1920, 1080]);
        }

        return false;
    }


    /**
     * Get the template file for the module
     * @return string
     */
    public function template(): string
    {
        return "dynamic-guide.blade.php";
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
