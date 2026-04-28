<?php
/**
 * Advanced Permissions Entries view class.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Views;

use CosmicGiant\Advanced_Permissions\Models\Entry_Permissions;
use GFAPI;
use GFCommon;
use GFFormsModel;
use GF_Query;
use GF_Query_Call;
use GF_Query_Column;
use GF_Query_Condition;
use GF_Query_JSON_Literal;
use GF_Query_Literal;
use GF_Query_Series;

/**
 * The Entries View class.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class Entries extends Base {

	const OPERATOR_CAN    = 'can';
	const OPERATOR_CANNOT = 'cannot';

	const VIEW_ALL       = 'all';
	const VIEW_LOGIC_ALL = 'logic_all';
	const VIEW_LOGIC_ANY = 'logic_any';

	/**
	 * The current Entry object.
	 *
	 * @since 3.0
	 *
	 * @var array|false
	 */
	private $entry;

	/**
	 * Entry Permissions rule for the current user.
	 *
	 * @since 3.0
	 *
	 * @var array|false|null
	 */
	private $rule;

	/**
	 * Add hooks.
	 *
	 * @since 3.0
	 */
	public function add_hooks() {

		// Exit if user is immune.
		if ( advancedpermissions()->get_current_user()->is_immune() ) {
			return;
		}

		add_action( 'wp_loaded', [ $this, 'process_entry_detail' ], 9999 );

		add_filter( 'gform_filter_links_entry_list', [ $this, 'filter_gform_filter_links_entry_list' ], 9999, 3 );
		add_filter( 'gform_search_criteria_entry_list', [ $this, 'filter_gform_search_criteria_entry_list' ], 9999, 2 );

		add_filter( 'gform_search_criteria_export_entries', [ $this, 'filter_gform_search_criteria_export_entries' ], 9999, 2 );

	}





	// # ENTRY DETAIL --------------------------------------------------------------------------------------------------

	/**
	 * Apply Entry Permissions on Entry Detail view.
	 *
	 * @since 3.0
	 */
	public function process_entry_detail() {

		if ( ! GFCommon::is_entry_detail() ) {
			return;
		}

		if ( ! $this->get_form_id() || ! $this->get_entry_id() ) {
			return;
		}

		$rule = $this->get_rule_for_user();

		if ( is_null( $rule ) ) {
			return;
		}

		$can_see_entry = false;

		if ( is_array( $rule ) ) {

			switch ( $rule['view'] ) {

				case self::VIEW_ALL:
					$can_see_entry = $rule['operator'] === self::OPERATOR_CAN;
					break;

				case self::VIEW_LOGIC_ALL:
				case self::VIEW_LOGIC_ANY:
					$form  = $this->get_current_form();
					$entry = $this->get_current_entry();

					$logic = [
						'logicType' => $rule['view'] === self::VIEW_LOGIC_ALL ? 'all' : 'any',
						'rules'     => $rule['logic'],
					];

					$evaluated = GFCommon::evaluate_conditional_logic( $logic, $form, $entry );

					if ( $rule['operator'] === self::OPERATOR_CAN ) {
						$can_see_entry = $evaluated;
					} else {
						$can_see_entry = ! $evaluated;
					}
					break;

			}

		}

		if ( ! $can_see_entry ) {
			wp_die( esc_html__( 'Access denied.', 'forgravity_advancedpermissions' ) );
		}

	}





	// # ENTRY LIST ----------------------------------------------------------------------------------------------------

	/**
	 * Update Entry List filter links entry counts to account for Entry Permissions.
	 *
	 * @since 3.0
	 *
	 * @param array $filter_links   An array of each filter link.
	 * @param array $form           The current Form object.
	 * @param bool  $include_counts Include entry counts.
	 *
	 * @return array
	 */
	public function filter_gform_filter_links_entry_list( $filter_links, $form, $include_counts ) {

		if ( ! $include_counts ) {
			return $filter_links;
		}

		$allowed_filters = [ 'all', 'unread', 'star', 'spam', 'trash' ];

		$rule = $this->get_rule_for_user();

		// If no rules exist for this form, return.
		if ( is_null( $rule ) ) {
			return $filter_links;
		}

		// If rule for user allows them to see all entries, return.
		if ( rgar( $rule, 'operator' ) === self::OPERATOR_CAN && rgar( $rule, 'view' ) === self::VIEW_ALL ) {
			return $filter_links;
		}

		// If no rules apply to this user or the user cannot see all entries, set entry count to 0.
		$get_counts = is_array( $rule ) && rgar( $rule, 'view' ) !== self::VIEW_ALL;

		// Get filter conditions for Entry Permission rule.
		$filters = $this->get_filter_conditions_for_rule();

		foreach ( $filter_links as $f => $filter_link ) {

			// If this is not a core filter link, skip.
			if ( ! in_array( $filter_link['id'], $allowed_filters ) ) {
				continue;
			}

			// If we are not getting counts, set to 0.
			if ( ! $get_counts ) {
				$filter_links[ $f ]['count'] = 0;
				continue;
			}

			$gf_query = new GF_Query();
			$gf_query->from( (int) $form['id'] );

			$status = $this->get_status_condition_for_filter_link( $filter_link['id'] );
			$gf_query->where( call_user_func_array( [ 'GF_Query_Condition', '_and' ], [ $status, $filters ] ) );

			$ids                         = $gf_query->get_ids();
			$filter_links[ $f ]['count'] = $gf_query->total_found;

		}

		return $filter_links;

	}

	/**
	 * Returns the GF_Query_Condition for the Entry List filter links.
	 *
	 * @since 3.0
	 *
	 * @param string $filter Filter link.
	 *
	 * @return GF_Query_Condition|GF_Query_Condition[]
	 */
	private function get_status_condition_for_filter_link( $filter ) {

		$conditions = [];

		if ( in_array( $filter, [ 'all', 'unread', 'star' ] ) ) {
			$conditions[] = new GF_Query_Condition(
				new GF_Query_Column( 'status' ),
				GF_Query_Condition::EQ,
				new GF_Query_Literal( 'active' )
			);
		}

		switch ( $filter ) {

			case 'spam':
				$conditions[] = new GF_Query_Condition(
					new GF_Query_Column( 'status' ),
					GF_Query_Condition::EQ,
					new GF_Query_Literal( 'spam' )
				);
				break;

			case 'star':
				$conditions[] = new GF_Query_Condition(
					new GF_Query_Column( 'is_starred' ),
					GF_Query_Condition::EQ,
					new GF_Query_Literal( '1' )
				);
				break;

			case 'trash':
				$conditions[] = new GF_Query_Condition(
					new GF_Query_Column( 'status' ),
					GF_Query_Condition::EQ,
					new GF_Query_Literal( 'trash' )
				);
				break;

			case 'unread':
				$conditions[] = new GF_Query_Condition(
					new GF_Query_Column( 'is_read' ),
					GF_Query_Condition::EQ,
					new GF_Query_Literal( '0' )
				);
				break;

		}

		if ( count( $conditions ) > 1 ) {
			return call_user_func_array( [ 'GF_Query_Condition', '_and' ], $conditions );
		}

		return $conditions[0];

	}

	/**
	 * Applies Entry Permissions on the Entry List view.
	 * If matching rule contains conditional logic, logic is applied via "gform_gf_query_sql" filter.
	 *
	 * @since 3.0
	 *
	 * @param array $search_criteria The search criteria array being filtered.
	 * @param int   $form_id         The current form ID.
	 *
	 * @return array
	 */
	public function filter_gform_search_criteria_entry_list( $search_criteria, $form_id ) {

		return $this->apply_rule_to_search_criteria( $search_criteria, $form_id );

	}

	/**
	 * Filter the SQL query fragments to apply Entry Permissions.
	 *
	 * @since 3.0
	 *
	 * @param array $clauses An array with all the SQL fragments: select, from, join, where, order, paginate.
	 *
	 * @return array
	 */
	public function filter_gform_gf_query_sql( $clauses ) {

		$filters_condition = $this->get_filter_conditions_for_rule();

		if ( ! empty( $filters_condition ) ) {
			$query = new GF_Query();
			// Conditions can be based on the entry meta table, which requires one or several joins.
			$joins            = $query->_join_infer( $filters_condition );
			$clauses['join'] .= count( $joins ) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $joins ) : '';
			// Create the where clause from the conditions.
			$clauses['where'] .= ' AND ' . $query->_where_unwrap( $filters_condition );
		}

		return $clauses;

	}

	/**
	 * Applies Entry Permissions to search criteria.
	 * If matching rule contains conditional logic, logic is applied via "gform_gf_query_sql" filter.
	 *
	 * @since 3.0
	 *
	 * @param array $search_criteria The search criteria array being filtered.
	 * @param int   $form_id         The current form ID.
	 *
	 * @return array
	 */
	private function apply_rule_to_search_criteria( $search_criteria, $form_id = false ) {

		if ( $form_id ) {
			$this->form_id = $form_id;
		}

		remove_filter( 'gform_gf_query_sql', [ $this, 'filter_gform_gf_query_sql' ], 9999 );

		$rule = $this->get_rule_for_user();

		// If no rules exist for this form, return.
		if ( is_null( $rule ) ) {
			return $search_criteria;
		}

		// If rule for user allows them to see all entries, return.
		if ( rgar( $rule, 'operator' ) === self::OPERATOR_CAN && rgar( $rule, 'view' ) === self::VIEW_ALL ) {
			return $search_criteria;
		}

		// If no rules apply to this user or the user cannot see all entries, set entry ID to a random value.
		if ( $rule === false || ( rgar( $rule, 'operator' ) === self::OPERATOR_CANNOT && rgar( $rule, 'view' ) === self::VIEW_ALL ) ) {

			$search_criteria['field_filters'][] = [
				'key'   => 'entry_id',
				'value' => uniqid(),
			];

			return $search_criteria;

		}

		add_filter( 'gform_gf_query_sql', [ $this, 'filter_gform_gf_query_sql' ], 9999 );

		return $search_criteria;

	}





	// # ENTRY LIST ----------------------------------------------------------------------------------------------------

	/**
	 * Applies Entry Permissions to Export Entries.
	 *
	 * @since 3.0
	 *
	 * @param array $search_criteria The search criteria array being filtered.
	 * @param int   $form_id         The current form ID.
	 *
	 * @return array
	 */
	public function filter_gform_search_criteria_export_entries( $search_criteria, $form_id ) {

		return $this->apply_rule_to_search_criteria( $search_criteria, $form_id );

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns the GF_Query_Condition object for the Entry Permission rule.
	 *
	 * @since 3.0
	 *
	 * @return GF_Query_Condition|GF_Query_Condition[]
	 */
	private function get_filter_conditions_for_rule() {

		$rule        = $this->get_rule_for_user();
		$use_inverse = $rule['operator'] === self::OPERATOR_CANNOT;

		$form    = $this->get_current_form();
		$form_id = $form['id'];

		$from = ! is_array( $form_id ) ? array( $form_id ) : $form_id;
		$from = array_map( 'absint', $from );

		$filters = [];

		foreach ( $rule['logic'] as $filter ) {

			$key   = rgar( $filter, 'fieldId' );
			$field = GFFormsModel::get_field( $form, $key );
			$value = rgar( $filter, 'value' );

			$operator = isset( $filter['operator'] ) ? $filter['operator'] : GF_Query_Condition::EQ;
			$operator = strtoupper( $operator );

			if ( $operator === 'CONTAINS' ) {
				$value = '%' . $value . '%';
			}

			$operator = $this->get_operator( $operator, $use_inverse );

			$is_numeric_filter = ( $field && $field->get_input_type() == 'number' ) || rgar( $filter, 'is_numeric' );
			if ( $operator != GF_Query_Condition::LIKE && $is_numeric_filter ) {
				if ( ! is_numeric( $value ) ) {
					$value = floatval( $value );
				}
				$filters[] = new GF_Query_Condition(
					GF_Query_Call::CAST( new GF_Query_Column( $key, $form_id ), GF_Query::TYPE_DECIMAL ),
					$operator,
					new GF_Query_Literal( $value )
				);
				continue;
			}

			if ( is_array( $value ) ) {
				foreach ( $value as &$v ) {
					$v = $field && $field->storageType == 'json' ? new GF_Query_JSON_Literal( (string) $v ) : new GF_Query_Literal( $v );
				}
				$value = new GF_Query_Series( $value );

				$filters[] = new GF_Query_Condition(
					new GF_Query_Column( $key, $form_id ),
					$operator,
					$value
				);

				continue;
			}

			$literal = $field && $field->storageType == 'json' ? new GF_Query_JSON_Literal( (string) $value ) : new GF_Query_Literal( (string) $value );

			$column    = count( $from ) > 1 ? new GF_Query_Column( $key ) : new GF_Query_Column( $key, $form_id );
			$filters[] = new GF_Query_Condition(
				$column,
				$operator,
				$literal
			);

		}

		$condition_mode = $rule['view'] === self::VIEW_LOGIC_ANY ? '_or' : '_and';

		if ( count( $filters ) > 1 ) {
			return call_user_func_array( [ 'GF_Query_Condition', $condition_mode ], $filters );
		}

		return $filters[0];

	}

	/**
	 * Returns the sanitized operator.
	 *
	 * @since 3.0
	 *
	 * @param string $operator Operator.
	 * @param bool   $inverse  Get inverse operator.
	 *
	 * @return string
	 */
	private function get_operator( $operator, $inverse = false ) {

		if ( $inverse ) {
			switch ( $operator ) {
				case 'CONTAINS':
				case 'LIKE':
					return GF_Query_Condition::NLIKE;
				case 'IS NOT':
				case 'ISNOT':
				case '<>':
					return GF_Query_Condition::EQ;
				case 'IS':
				case '=':
					return GF_Query_Condition::NEQ;
				case 'NOT IN':
					return GF_Query_Condition::IN;
				case 'IN':
					return GF_Query_Condition::NIN;

			}
		}

		switch ( $operator ) {
			case 'CONTAINS':
			case 'LIKE':
				return GF_Query_Condition::LIKE;
			case 'IS NOT':
			case 'ISNOT':
			case '<>':
				return GF_Query_Condition::NEQ;
			case 'IS':
			case '=':
				return GF_Query_Condition::EQ;
			case 'NOT IN':
				return GF_Query_Condition::NIN;
			case 'IN':
				return GF_Query_Condition::IN;
		}

		return GF_Query_Condition::EQ;

	}

	/**
	 * Returns the current Entry object.
	 *
	 * @since 3.0
	 *
	 * @return array|false
	 */
	private function get_current_entry() {

		if ( isset( $this->entry ) ) {
			return $this->entry;
		}

		if ( ! $entry_id = $this->get_entry_id() ) { // phpcs:ignore
			return false;
		}

		$entry       = GFAPI::get_entry( $entry_id );
		$this->entry = is_wp_error( $entry ) ? false : $entry;

		return $this->entry;

	}

	/**
	 * Returns the current Entry ID.
	 *
	 * @since 3.0
	 *
	 * @return false|int Entry ID or false if not found.
	 */
	private function get_entry_id() {

		return rgget( 'lid' ) ? (int) $_GET['lid'] : false; // phpcs:ignore

	}

	/**
	 * Returns the Entry Permissions rule for the current user.
	 *
	 * @since 3.0
	 *
	 * @return array|false|null
	 */
	private function get_rule_for_user() {

		if ( isset( $this->rule ) ) {
			return $this->rule;
		}

		if ( ! $form_id = $this->get_form_id() ) { // phpcs:ignore
			return null;
		}

		$this->rule = Entry_Permissions::get( $form_id )->get_rule_for_user();

		return $this->rule;

	}

}
