<?php

namespace Visit;

class App
{
    /**
     * The unique instance of the plugin.
     *
     * @var Visit\App
     */
    private static $instance;

    /**
     * Gets an instance of our plugin.
     *
     * @return Visit\App
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    public function __construct()
    {

        if (class_exists('Visit\PostTypes')) {
            new PostTypes();
        }
        if (class_exists('Visit\Taxonomies')) {
            new Taxonomies();
        }
        if (class_exists('Visit\Acf')) {
            new Acf();
        }

        add_action('acf/save_post', [$this, 'setPostSingleShowFeaturedImage'], 1, 1);

        // Allow setting quick link colors
        add_filter('Municipio/Navigation/Item', [$this, 'quickLinkColors'], 10, 3);

        // Display breadcrumbs after the content on all posts
        add_filter('Municipio/Partials/Navigation/HelperNavBeforeContent', '__return_false');

        // Place Quicklinks below the content on all places
        add_filter('Municipio/QuickLinksPlacement', [$this, 'placeQuicklinksAfterContent'], 10, 2);

        // Only display current term and it's children in secondary query filter
        add_filter('Municipio/secondaryQuery/getTermsArgs', [$this, 'getTermsArgs'], 10, 2);

        //Handle search in the quicklinks menu
        add_filter('Municipio/Navigation/Item', [$this, 'quicklinksSearchMenuItem'], 10, 3);

        // Unlinked terms with term icons from custom taxonomy "other"
        add_filter('Municipio/Controller/SingularPlace/listing', [$this, 'appendListingItems'], 11, 2);
        // Order listing items
        add_filter('Municipio/Controller/SingularPlace/listing', [$this, 'orderListingItems'], 99, 1);

        // Print Bike Approved Accommodation info on places with the term
        add_filter('Municipio/Helper/Post/postObject', [$this, 'appendBikeApprovedAccommodationInfo'], 10, 1);

        add_filter('Municipio/Archive/showFilter', [$this, 'hideFiltersOnTerms'], 10, 2);
    }

    public function hideFiltersOnTerms($displayFilters, $args)
    {
        if (is_tax()) {
            $displayFilters = false;
        }
        return $displayFilters;
    }


    public function appendListingItems($listing, $fields)
    {
        if (!empty($fields['other']) && class_exists('\Municipio\Helper\Listing')) {
            $listing['other'] = [];
            foreach (\Municipio\Helper\Listing::getTermsWithIcon($fields['other']) as $term) {
                if (!is_array($term->icon)) {
                    continue;
                }
                $listing['other'][$term->slug] = \Municipio\Helper\Listing::createListingItem(
                    $term->name,
                    '',
                    $term->icon,
                );
            }
        }
        return $listing;
    }

    public function orderListingItems($listing)
    {

        $orderedListing = [];

        if (isset($listing['location'])) {
            $orderedListing['location'] = $listing['location'];
        }
        if (isset($listing['phone'])) {
            $orderedListing['phone'] = $listing['phone'];
        }
        if (isset($listing['website'])) {
            $orderedListing['website'] = $listing['website'];
        }
        if (isset($listing['other'])) {
            if (is_array($listing['other'])) {
                foreach ($listing['other'] as $key => $item) {
                    $orderedListing[$key] = $item;
                }
            } else {
                $orderedListing['other'] = $listing['other'];
            }
        }

        return $orderedListing;
    }

    /**
     * Adds search icon to main menu
     *
     * @param array     $data          Array containing the menu
     * @param string    $identifier    What menu being filtered
     *
     * @return array
     */
    public function quicklinksSearchMenuItem($data, $identifier, $pageId)
    {
        if ($data['href'] == '#search' && 'single' === $identifier) {
            $data = array_merge(
                $data,
                [
                "id" => "search-icon",
                "isSearch" => true,
                "classList" => ["c-nav__item--search"],
                "attributeList" => [
                    'aria-label' => __("Search", 'municipio'),
                    'data-open' => 'm-search-modal__trigger',
                ],
                ]
            );
            if (is_front_page() || is_search()) {
                $data["classList"] = ["u-display--none"];
            }
        }
        return $data;
    }
    public function placeQuicklinksAfterContent($placement, $postId)
    {
        if (get_post_type($postId) === 'place') {
            return "below_content";
        }
        return $placement;
    }
    public function quickLinkColors($item)
    {
        $item['color'] = get_field('menu_item_color', $item['id']);
        return $item;
    }

    // Always set post_single_show_featured_image from "Display settings" to true
    public function setPostSingleShowFeaturedImage($postId)
    {
        $_POST['acf']['field_56c33e148efe3'] = 1;
    }
    /**
     * @param array $args The arguments passed to the get_terms() function.
     * @param array $data The data that is passed to the template.
     *
     * @return array An array of terms.
     */
    public function getTermsArgs(array $args = [], array $data = [])
    {

        $pageForTerms = $this->isPageForTerm();

        if (empty($pageForTerms) || !in_array($args['taxonomy'], ['activity', 'cuisine'])) {
            return $args;
        }

        if ($args['taxonomy'] === 'cuisine') {
            $pageForTerms = $this->isPageForTerm();
            foreach ($pageForTerms as $termId) {
                $term = get_term($termId);
                if (is_a($term, 'WP_Term') && 'activity' == $term->taxonomy) {
                    if ($this->isFoodRelated($term->slug)) {
                        // We've found at least one food-related activity on this page,
                        // so we can return the args for the cuisine filter and display it.
                        return $args;
                    }
                }
            }
            return false;
        }

        if (isset($args['taxonomy']) && $args['taxonomy'] == 'activity') {
            $termIdsToInclude = [];
            foreach ($pageForTerms as $termId) {
                $term = get_term($termId);
                if (is_a($term, 'WP_Term')) {
                    $termChildren = get_term_children($termId, $term->taxonomy);
                    if (!empty($termChildren) && !is_wp_error($termChildren)) {
                        $termIdsToInclude = array_merge($termIdsToInclude, $termChildren);
                    }
                }
            }
            // No child terms found, no need to display the filter.
            if (empty($termIdsToInclude)) {
                return false;
            }
            // Child term found, include only those in the filter.
            $args['include'] = $termIdsToInclude;
        }

        return $args;
    }

    /**
     * If the post has a value for the ACF field "is_page_for_term", return the value of that field.
     * Otherwise, return false.
     *
     * @param int postId The post ID of the page you want to check. If you don't pass this, it will use
     * the current page.
     *
     * @return An array of term objects.
     */
    public function isPageForTerm(int $postId = 0)
    {
        if (!$postId) {
            $postId = get_queried_object_id();
        }
        $terms = (array) get_field('is_page_for_term', $postId);
        if (empty($terms)) {
            return false;
        }
        return $terms;
    }
    /**
     * Checks if a given term name is related to a food activity.
     *
     * @param string termSlug The slug of the term you want to check.
     *
     * @return A boolean value.
     */
    public function isFoodRelated(string $termSlug = '')
    {
        return in_array(
            $termSlug,
            [
                'mat-dryck',
                'ata-dricka',
                'mat-och-dryck',
                'ata-och-dricka',
                'food',
                'food-beverage',
                'food-and-beverage',
            ]
        );
    }
    /**
     * Checks if a given term name is the term for the "Bike Approved Accomodation" certification.
     *
     * @param string termSlug The slug of the term you want to check.
     *
     * @return A boolean value.
     */
    public function isBikeApprovedAccommodation(string $termSlug = '')
    {
        return in_array(
            $termSlug,
            [
                'bike-approved-accommodation',
                'bike-approved-acommodation', // common misspelling of "accommodation"
                'bike-approved-accomodation', // common misspelling of "accommodation"
                'bike-approved',
                'bike-friendly-accommodation',
                'bike-friendly-acommodation', // common misspelling of "accommodation"
                'bike-friendly-places',
                'bike-friendly-place',
                'bike-friendly-location',
            ]
        );
    }

    /**
     * The function appends information about bike-approved accommodations to a post object if it has a
     * certain term.
     *
     * @param object $postObject
     *
     * @return $postObject
     */
    public function appendBikeApprovedAccommodationInfo($postObject)
    {
        if (property_exists($postObject, 'post_content_filtered')) {
            $terms = get_the_terms($postObject->ID, 'other');
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    if ($this->isBikeApprovedAccommodation($term->slug)) {
                        $description = get_field('description', $term) ?? term_description($term) ?? false;
                        $postObject->post_content_filtered .= apply_filters('the_content', \render_blade_view(
                            'partials.bike-approved-accommodation',
                            [
                                'description' => str_replace(
                                    ["[plats]","[place]"], // Replace with the name of the place being displayed.
                                    $postObject->post_title,
                                    $description
                                )
                            ]
                        ));
                        break;
                    }
                }
            }
        }
        return $postObject;
    }
}
