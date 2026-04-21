<?php

namespace ModularityDynamicGuides;

class App
{
    private $cacheBust;
    
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'registerModule'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdmin'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueFrontend'));
        add_action('acf/validate_save_post', array($this, 'validateOutcomes'));

        $this->cacheBust = new \ModularityDynamicGuides\Helper\CacheBust();
    }

    /**
     * Validate outcomes before saving ACF post.
     */
    public function validateOutcomes() {
        global $_POST;
        if (empty($_POST['acf']['field_65b8b17f91cdf'])) return;

        $outcomes = [];
        $hiddenOutcomes = [];

        if (!empty($_POST['acf']['field_65b78add784cd']) && is_array($_POST['acf']['field_65b78add784cd'])) {
            $stepChoices = $this->getStepChoices($_POST['acf']['field_65b78add784cd']);

            
            if (!empty($stepChoices)) {
                $outcomes = $this->calculateOutcomes($stepChoices);
            }
        }
        
        if (!empty($_POST['acf']['field_65ba49cbdb950'])) {
            $hiddenOutcomes = $this->getHiddenOutcomes($_POST['acf']['field_65ba49cbdb950']);
        }

        
        $outcomesDiff = $this->compareArrays($outcomes, $hiddenOutcomes);
        $isError = false;

        if (!empty($outcomesDiff)) {
            $isError = $this->getOutcomesDiffString($outcomesDiff);
        }

        if ($isError) {
            acf_add_validation_error('acf[field_65b8b17f91cdf]', $isError);
        }
    }

    /**
     * Get hidden outcomes from JSON string.
     *
     * @param string $hiddenOutcomesJson JSON string containing hidden outcomes.
     * @return array Hidden outcomes array.
     */
    private function getHiddenOutcomes(string $hiddenOutcomesJson): array {
        $hiddenOutcomesJson = stripslashes($hiddenOutcomesJson);
        
        if (!empty($hiddenOutcomesJson)) {
            $hiddenOutcomesArr = json_decode($hiddenOutcomesJson, true);

            if (!empty($hiddenOutcomesArr) && is_array($hiddenOutcomesArr)) {
                return $hiddenOutcomesArr;
            }
        }

        return [];
    }

    /**
     * Get step choices based on selected steps.
     *
     * @param array $steps Selected steps.
     * @return array Step choices array.
     */
    private function getStepChoices(array $steps): array {
        $stepChoices = [];
            foreach ($steps as $step) {
                if (
                    !empty($step['field_65b78b84784ce']) && 
                    is_array($step['field_65b78b84784ce'])
                ) {
                    $choices = [];
                    foreach ($step['field_65b78b84784ce'] as $choice) {
                        if ($choice['field_65b78b92784cf']) {
                            $choices[] = $choice['field_65b78b92784cf'];
                        }
                    }

                    if (!empty($choices) && !empty($step['field_65b7993d1aba6'])) {
                        $stepChoices[] = [
                            'step' => $step['field_65b7993d1aba6'],
                            'choices' => $choices
                        ];
                    }
                }
            }

        return $stepChoices;
    }

    /**
     * Generate HTML string for missing outcomes differences.
     *
     * @param array $outcomesDiff Array of missing outcomes differences.
     * @return string HTML string for missing outcomes.
     */
    private function getOutcomesDiffString(array $outcomesDiff): string {
        $diff = "";
        foreach ($outcomesDiff as $outcome) {
            if (is_array($outcome)) {
                $outcomeDiffString = implode(', ', array_map(
                    function ($key, $value) {
                        return "<b>$key:</b> $value";
                    },
                    array_keys($outcome),
                    $outcome
                ));

                $diff .= '<li style="font-size: 0.9rem;">' . $outcomeDiffString . '</li>';
            }
        }

        return $diff = '<b style="font-size: 1.1rem;">Missing outcomes:</b><ol>' . $diff . '</ol>';
    }

    /**
     * Compare two arrays and return missing outcomes.
     *
     * @param array $outcomes Array of outcomes.
     * @param array $hiddenOutcomes Array of hidden outcomes.
     * @return array Missing outcomes.
     */
    private function compareArrays(array $outcomes, array $hiddenOutcomes) {
        $missingOutcomes = [];
        foreach ($outcomes as $outcome) {
            if (!in_array($outcome, $hiddenOutcomes)) {
                $missingOutcomes[] = $outcome;
            }
        }

        return $missingOutcomes;
    }

    /**
     * Recursively calculate all possible outcomes based on steps and choices.
     *
     * @param array $steps Steps and choices array.
     * @param array $currentOutcome Current outcome being calculated.
     * @return array All possible outcomes.
     */
    private function calculateOutcomes(array $steps, array $currentOutcome = []): array {
        $allOutcomes = [];

        if (empty($steps)) {
            return [$currentOutcome];
        }
    
        $currentStep = array_shift($steps);
    
        foreach ($currentStep['choices'] as $choice) {
            $outcomes = $this->calculateOutcomes($steps, array_merge($currentOutcome, [$currentStep['step'] => $choice]));
    
            $allOutcomes = array_merge($allOutcomes, $outcomes);
        }
    
        return $allOutcomes;
    }

    /**
     * Enqueue admin css and js
     * @return void
    */
    public function enqueueAdmin() 
    {
        wp_register_script(
            'modularity-dynamic-guides-admin-js',
            MODULARITYDYNAMICGUIDES_URL . '/dist/' .
            $this->cacheBust->name('js/modularity-dynamic-guides-admin.js'),
            ['acf-input']
        );

        wp_enqueue_script('modularity-dynamic-guides-admin-js');

        wp_register_style(
            'modularity-dynamic-guides-admin-css',
            MODULARITYDYNAMICGUIDES_URL . '/dist/' .
            $this->cacheBust->name('css/modularity-dynamic-guides-admin.css')
        );

        wp_enqueue_style('modularity-dynamic-guides-admin-css');

    }

    /**
     * Enqueue frontend css and js
     * @return void
     */
    public function enqueueFrontend()
    {
        wp_register_style(
            'modularity-dynamic-guides-css',
            MODULARITYDYNAMICGUIDES_URL . '/dist/' .
            $this->cacheBust->name('css/modularity-dynamic-guides.css')
        );

        wp_enqueue_style('modularity-dynamic-guides-css');

        wp_register_script(
            'modularity-dynamic-guides-js',
            MODULARITYDYNAMICGUIDES_URL . '/dist/' .
            $this->cacheBust->name('js/modularity-dynamic-guides.js')
        );

        wp_enqueue_script('modularity-dynamic-guides-js');
    }

    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYDYNAMICGUIDES_PATH . 'source/php/Module/',
                'DynamicGuides'
            );
        }
    }
}
