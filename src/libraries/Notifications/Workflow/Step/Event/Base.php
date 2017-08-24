<?php
/**
 * @package     PublishPress\Notifications
 * @author      PressShack <help@pressshack.com>
 * @copyright   Copyright (C) 2017 PressShack. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace PublishPress\Notifications\Workflow\Step\Event;

use PublishPress\Notifications\Workflow\Step\Base as Base_Step;
use PublishPress\Notifications\Traits\Metadata;

class Base extends Base_Step {

	use Metadata;

	const META_KEY_SELECTED = '_psppno_evtundefined';

	const META_VALUE_SELECTED = 'undefined';

	/**
	 * Cache for the list of filters
	 *
	 * @param array
	 */
	protected $cache_filters = [];
	/**
	 * The constructor
	 */
	public function __construct() {
		$this->attr_prefix   = 'event';
		$this->twig_template = 'workflow_event_field.twig';

		parent::__construct();

		// Add the event filters to the metabox template
		add_filter(
			"publishpress_notif_workflow_metabox_context_{$this->attr_prefix}_{$this->name}",
			[ $this, 'filter_workflow_metabox_context' ]
		);

		// Add the fitler to the run workflow query args
		add_filter( 'publishpress_notif_run_workflow_meta_query', [ $this, 'filter_run_workflow_query_args' ], 10, 2 );
		// Add filter to return the metakey representing if it is selected or not
		add_filter( 'psppno_events_metakeys', [ $this, 'filter_events_metakeys' ] );
	}

	/**
	 * Filters the context sent to the twig template in the metabox
	 *
	 * @param array $template_context
	 */
	public function filter_workflow_metabox_context( $template_context ) {
		$template_context['name']          = esc_attr( "publishpress_notif[{$this->attr_prefix}][]" );
		$template_context['id']            = esc_attr( "publishpress_notif_{$this->attr_prefix}_{$this->name}" );
		$template_context['event_filters'] = $this->get_filters();

		$meta = (int) $this->get_metadata( static::META_KEY_SELECTED, true );

		$template_context['meta'] = [
			'selected' => (bool) $meta,
		];

		return $template_context;
	}

	/**
	 * Method to return a list of fields to display in the filter area
	 *
	 * @param array
	 *
	 * @return array
	 */
	protected function get_filters( $filters = [] ) {
		if ( ! empty( $this->cache_filters ) ) {
			return $this->cache_filters;
		}

		/**
		 * Filters the list of filters for the event Comment in the workflow.
		 *
		 * @param array $filters
		 */
		$this->cache_filters = apply_filters( "publishpress_notif_workflow_event_{$this->name}_filters", $filters );

		return $this->cache_filters;
	}

	/**
	 * Method called when a notification workflow is saved.
	 *
	 * @param int      $id
	 * @param WP_Post  $post
	 */
	public function save_metabox_data( $id, $post ) {
		if ( ! isset( $_POST['publishpress_notif'] )
			|| ! isset( $_POST['publishpress_notif']['event'] ) ) {
			// Assume it is disabled
			update_post_meta( $id, static::META_KEY_SELECTED, false );
		}

		$params = $_POST['publishpress_notif'];

		if ( isset( $params['event'] ) ) {
			// Is selected in the events?
			$selected = in_array( static::META_VALUE_SELECTED, $params['event'] );
			update_post_meta( $id, static::META_KEY_SELECTED, $selected );

			// Process the filters
			$filters = $this->get_filters();
			if ( ! empty( $filters ) ) {
				foreach ( $filters as $filter ) {
					$filter->save_metabox_data( $id, $post );
				}
			}
		}
	}

	/**
	 * Filters and returns the arguments for the query which locates
	 * workflows that should be executed.
	 *
	 * @param array $query_args
	 * @param array $action_args
	 * @return array
	 */
	public function filter_run_workflow_query_args( $query_args, $action_args ) {
		return $query_args;
	}

	/**
	 * Add the metakey to the array to be processed
	 *
	 * @param array $metakeys
	 *
	 * @return array
	 */
	public function filter_events_metakeys( $metakeys ) {
		$metakeys[ static::META_KEY_SELECTED ] = $this->label;

		return $metakeys;
	}
}