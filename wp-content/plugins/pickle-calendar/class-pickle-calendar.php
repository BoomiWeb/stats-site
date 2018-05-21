<?php
/**
 * Main Pickle Calendar class
 *
 * @package PickleCalendar
 * @since   1.0.0
 */

/**
 * Pickle_Calendar class.
 */
class Pickle_Calendar {

    /**
     * Construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_bscal_nav', array( $this, 'ajax_nav' ) );
        add_action( 'wp_ajax_nopriv_bscal_nav', array( $this, 'ajax_nav' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts_styles' ) );

        add_shortcode( 'pickle_calendar', array( $this, 'shortcode' ) );
    }

    /**
     * Scripts and styles function.
     *
     * @access public
     * @return void
     */
    public function scripts_styles() {
        wp_register_script( 'pickle-calendar-script', PICKLE_CALENDAR_URL . 'js/calendar.min.js', array( 'jquery' ), picklecalendar()->version, true );

        wp_localize_script(
            'pickle-calendar-script', 'pickleCalOpts', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            )
        );

        wp_enqueue_script( 'pickle-calendar-script' );

        wp_register_style( 'pickle-calendar-style', PICKLE_CALENDAR_URL . 'css/calendar.min.css', '', picklecalendar()->version );
    }

    /**
     * Main calendar function.
     *
     * @access public
     * @param string $args (default: '').
     * @return html
     */
    public function calendar( $args = '' ) {
        $html = null;

        $default_args = array(
            'month' => date( 'n' ),
            'year' => date( 'Y' ),
            'days_of_week_format' => 'D',
            'month_format' => 'F',
            'echo' => true,
            'show_filters' => true,
        );
        $args = wp_parse_args( $args, $default_args );
        $filters = array();

        if ( $args['show_filters'] ) {
            $filters = apply_filters( 'pickle_calendar_filters', $this->filters( $args ), $args );
        }

        wp_enqueue_style( 'pickle-calendar-style' );

        $html .= apply_filters( 'pickle_calendar_before_calendar', '', $args );

        if ( $args['show_filters'] ) {
            $html .= $this->filters_display( $filters['tabs'] );
        }

        $html .= '<div class="col-xs-12 pickle-calendar">';
            $html .= $this->create_header( $args['month'], $args['year'], $args['month_format'] );
            $html .= $this->days_of_week( $args['days_of_week_format'] );
            $html .= $this->draw_calendar( $args['month'], $args['year'] );
        $html .= '</div>';

        if ( $args['show_filters'] ) {
            $html .= $this->filters_display( $filters['checkboxes'] );
        }

        $html .= apply_filters( 'pickle_calendar_after_calendar', '', $args );

        if ( $args['echo'] ) {
            echo esc_html( $html );
        }

        return $html;
    }

    /**
     * Calendar header function.
     *
     * @access protected
     * @param mixed $month (month).
     * @param mixed $year (year).
     * @param mixed $month_format (month format).
     * @return html
     */
    protected function create_header( $month, $year, $month_format ) {
        $html = null;

        $html .= '<div class="row header">';
            $html .= '<div class="col-xs-2 prev"><a class="cal-nav" href="#" data-month="' . $this->prev_month( $month ) . '" data-year="' . $this->nav_year( $month, $year, 'prev' ) . '">&#10094;</a></div>';
            $html .= '<div class="col-xs-5 month">' . $this->month( $month, $month_format ) . '</div>';
            $html .= '<div class="col-xs-3 year">' . $year . '</div>';
            $html .= '<div class="col-xs-2 next"><a class="cal-nav" href="#" data-month="' . $this->next_month( $month ) . '" data-year="' . $this->nav_year( $month, $year, 'next' ) . '">&#10095;</a></div>';
        $html .= '</div>';

        return apply_filters( 'pickle_calendar_create_header', $html, $month, $year );
    }

    /**
     * Calendar days of the week function.
     *
     * @access protected
     * @param mixed $format (format of day).
     * @return html
     */
    protected function days_of_week( $format ) {
        $html = null;
        $dow = array( 0, 1, 2, 3, 4, 5, 6 );
        $dow_formatted = array_map( array( $this, 'format_day' ), $dow );

        $html .= '<div class="row weekdays">';

        foreach ( $dow_formatted as $day ) :
            $html .= '<div class="dow">' . $day . '</div>';
            endforeach;

        $html .= '</div>';

        return apply_filters( 'pickle_calendar_weekdays', $html, $dow );
    }

    /**
     * Formats day function.
     *
     * @access protected
     * @param mixed $day (the day number).
     * @return php date
     */
    protected function format_day( $day ) {
        $format = apply_filters( 'bscal_day_format', 'D' );

        return date( $format, strtotime( "Sunday + $day Days" ) );
    }

    /**
     * Formats month function.
     *
     * @access protected
     * @param mixed  $month (number).
     * @param string $format (default: 'F').
     * @return php date
     */
    protected function month( $month, $format = 'F' ) {
        return date( $format, mktime( 0, 0, 0, $month ) );
    }

    /**
     * Draws the calendar.
     *
     * @access protected
     * @param mixed $month (number).
     * @param mixed $year (number).
     * @return html
     */
    protected function draw_calendar( $month, $year ) {
        $html = null;

        // days and weeks vars now.
        $running_day = date( 'w', mktime( 0, 0, 0, $month, 1, $year ) ); // numeric rep of week.
        $days_in_month = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
        $days_in_this_week = 1;
        $day_counter = 0;
        $dates_array = array();
        $current_date = date( 'Y-m-d' );

        $html .= '<div class="cal-wrap">';

        // row for week one.
        $html .= '<div class="row">';

        // print "blank" days until the first of the current week.
        for ( $x = 0; $x < $running_day; $x++ ) :
            $html .= '<div class="calendar-day np"></div>';
            $days_in_this_week++;
        endfor;

        // keep going with days.
        for ( $list_day = 1; $list_day <= $days_in_month; $list_day++ ) :
            $classes = array( 'calendar-day' );
            $pref_date = date( 'Y-m-d', strtotime( "$year-$month-$list_day" ) );

            if ( $pref_date == $current_date ) {
                $classes[] = 'today';
            }

            if ( 0 == $running_day ) :
                $classes[] = 'first-of-week';
                $eow_day = date( 'Y-m-d', strtotime( $pref_date . ' +6 days' ) );
            endif;

            if ( 6 == $running_day ) {
                $classes[] = 'last-of-week';
            }

            $html .= '<div class="' . implode( ' ', $classes ) . '">';
                $html .= '<div class="day-number">' . $list_day . '</div>'; // add day number.

                $html .= apply_filters( 'pickle_calendar_single_day', $this->add_date_info( $pref_date ), $pref_date );

            $html .= '</div>';

            if ( 6 == $running_day ) :
                $html .= '</div>';

                if ( ( $day_counter + 1 ) != $days_in_month ) :
                    $html .= '<div class="row">';
                endif;

                $running_day = -1;
                $days_in_this_week = 0;
            endif;

            $days_in_this_week++;
            $running_day++;
            $day_counter++;
        endfor;

        // finish the rest of the days in the week.
        if ( $days_in_this_week < 8 ) :
            for ( $x = 1; $x <= ( 8 - $days_in_this_week ); $x++ ) :
                $html .= '<div class="calendar-day np"></div>';
            endfor;
        endif;

        // final row.
        $html .= '</div>';

        $html .= '</div>'; // cal-wrap.

        return $html;
    }

    /**
     * Gets the next month.
     *
     * @access protected
     * @param mixed $month (number).
     * @return integer
     */
    protected function next_month( $month ) {
        $next_month = $month + 1;

        if ( $next_month > 12 ) {
            $next_month = 1;
        }

        return $next_month;
    }

    /**
     * Gets previous month.
     *
     * @access protected
     * @param mixed $month (number).
     * @return integer
     */
    protected function prev_month( $month ) {
        $prev_month = $month - 1;

        if ( $prev_month < 1 ) {
            $prev_month = 12;
        }

        return $prev_month;
    }

    /**
     * Gets the nav year.
     *
     * @access protected
     * @param mixed $month (number).
     * @param mixed $year (number).
     * @param mixed $action (string: prev/next).
     * @return year
     */
    protected function nav_year( $month, $year, $action ) {
        $nav_year = $year;

        switch ( $action ) :
            case 'prev':
                if ( $this->prev_month( $month ) == 12 ) {
                    $nav_year = $year - 1;
                }
                break;
            case 'next':
                if ( $this->next_month( $month ) == 1 ) {
                    $nav_year = $year + 1;
                }
                break;
        endswitch;

        return $nav_year;
    }

    /**
     * Add date info function.
     *
     * @access protected
     * @param string $date (default: '').
     * @return html
     */
    protected function add_date_info( $date = '' ) {
        $content = '';
        $events = $this->get_events( $date );

        if ( empty( $events ) ) {
            return;
        }

        foreach ( $events as $key => $event_id ) :
            $classes = array( 'event-' . $event_id );

            if ( $this->event_is_multiday( $event_id, $date ) ) :
                $classes[] = 'multiday';

                if ( $this->is_start_date( $event_id, $date ) ) :
                    $classes[] = 'start';
                elseif ( $this->is_end_date( $event_id, $date ) ) :
                    $classes[] = 'end';
                endif;
            else :
                $classes[] = 'single';
            endif;

            // add terms as classes.
            $classes = $this->add_terms_classes( $event_id, $classes );

            $title = '<a href="' . get_permalink( $event_id ) . '">' . get_the_title( $event_id ) . '</a>';
            $text = apply_filters( 'pickle_calendar_event_title', $title, $event_id );

            if ( $this->event_is_multiday( $event_id, $date ) && ! $this->is_start_date( $event_id, $date ) ) {
                $text = '&nbsp;';
            }

            $content .= '<div class="pickle-calendar-event ' . implode( ' ', $classes ) . '" data-event-id="' . $event_id . '" data-event-day-number="' . $key . '" data-event-date="' . $date . '" data-event-total-days=' . $this->total_days( $event_id, $date ) . '>' . $text . '</div>';

        endforeach;

        return $content;
    }

    /**
     * Is it a start date?
     *
     * @access public
     * @param int    $id (default: 0).
     * @param string $date (default: '').
     * @return boolean
     */
    public function is_start_date( $id = 0, $date = '' ) {
        global $wpdb;

        $meta_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->postmeta WHERE $wpdb->postmeta.post_id = %d AND meta_value = %s", $id, $date ) );

        if ( strpos( $meta_key, '_start_date_' ) !== false ) {
            return true;
        }

        return false;
    }

    /**
     * Is it an end date?
     *
     * @access public
     * @param int    $id (default: 0).
     * @param string $date (default: '').
     * @return boolean
     */
    public function is_end_date( $id = 0, $date = '' ) {
        global $wpdb;

        $meta_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->postmeta WHERE $wpdb->postmeta.post_id = %d AND meta_value = %s", $id, $date ) );

        if ( strpos( $meta_key, '_end_date_' ) !== false ) {
            return true;
        }

        return false;
    }

    /**
     * Is the event multiple days?
     *
     * @access public
     * @param int    $event_id (default: 0).
     * @param string $date (default: '').
     * @return boolean
     */
    public function event_is_multiday( $event_id = 0, $date = '' ) {
        global $wpdb;

        $id = $wpdb->get_var(
            $wpdb->prepare(
                "
			SELECT $wpdb->postmeta.post_id
			FROM $wpdb->postmeta
			INNER JOIN $wpdb->postmeta AS mt1 ON ( wp_postmeta.post_id = mt1.post_id ) 
			WHERE $wpdb->postmeta.post_id = %d
				AND ( REPLACE($wpdb->postmeta.meta_key, '_start_date_', '') = REPLACE(mt1.meta_key, '_end_date_', '') )
				AND $wpdb->postmeta.meta_value != mt1.meta_value
				AND ( $wpdb->postmeta.meta_value >= %s OR mt1.meta_value >= %s)
            ",
                $event_id,
                $date,
                $date
            )
        );

        if ( $id ) {
            return true;
        }

        return false;
    }

    /**
     * Get total days.
     *
     * @access public
     * @param int    $event_id (default: 0).
     * @param string $start_date (default: '').
     * @return ineger
     */
    public function total_days( $event_id = 0, $start_date = '' ) {
        global $wpdb;

        $meta_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->postmeta WHERE $wpdb->postmeta.post_id = %d AND $wpdb->postmeta.meta_value = %s", $event_id, $start_date ) );
        $date_id = str_replace( '_start_date_', '', $meta_key );
        $end_date_id = '_end_date_' . $date_id;
        $end_date = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE $wpdb->postmeta.post_id = %d AND $wpdb->postmeta.meta_key = %s", $event_id, $end_date_id ) );

        $start_ts = strtotime( $start_date );
        $end_ts = strtotime( $end_date );
        $diff = $end_ts - $start_ts;

        return round( $diff / 86400 ) + 1;
    }

    /**
     * Add terms classes function.
     *
     * @access protected
     * @param mixed $event_id (integer).
     * @param mixed $classes (array).
     * @return array
     */
    protected function add_terms_classes( $event_id, $classes ) {
        if ( ! isset( picklecalendar()->settings['taxonomies'] ) || empty( picklecalendar()->settings['taxonomies'] ) ) {
            return $classes;
        }

        $taxonomies = array();

        foreach ( picklecalendar()->settings['taxonomies'] as $taxonomy ) :
            $taxonomies[] = $taxonomy['slug'];
        endforeach;

        $terms = wp_get_post_terms( $event_id, $taxonomies );

        foreach ( $terms as $term ) :
            $classes[] = $term->slug;
        endforeach;

        return $classes;
    }

    /**
     * Get the days events.
     *
     * @access protected
     * @param string $date (default: '').
     * @param string $args (default: '').
     * @return array
     */
    protected function get_events( $date = '', $args = '' ) {
        global $wpdb;

        $default_args = array(
            'post_type' => 'pcevent',
        );
        $args = wp_parse_args( $args, $default_args );

        // AND wp_postmeta.meta_value != mt1.meta_value.
        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "
			SELECT wp_posts.ID
			FROM $wpdb->posts
			INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) 
			INNER JOIN $wpdb->postmeta AS mt1 ON ( $wpdb->posts.ID = mt1.post_id ) 
			WHERE 1=1
				AND ( ( $wpdb->postmeta.meta_key LIKE %s AND CAST($wpdb->postmeta.meta_value AS DATE) <= %s ) AND ( mt1.meta_key LIKE %s AND CAST(mt1.meta_value AS DATE) >= %s ) ) 
				AND ( REPLACE($wpdb->postmeta.meta_key, '_start_date_', '') = REPLACE(mt1.meta_key, '_end_date_', '') )
				AND $wpdb->posts.post_type = 'pcevent' 
				AND (($wpdb->posts.post_status = 'publish')) 
			GROUP BY $wpdb->posts.ID
			ORDER BY $wpdb->postmeta.meta_value ASC
            ",
                '_start_date_%',
                $date,
                '_end_date_%',
                $date
            )
        );

        if ( empty( $post_ids ) ) {
            return;
        }

        $post_ids = apply_filters( 'pickle_calendar_get_events', $post_ids, $date );

        return $post_ids;
    }

    /**
     * Get events in week.
     *
     * @access protected
     * @param string $args (default: '').
     * @return array
     */
    protected function get_events_in_week( $args = '' ) {
        global $wpdb;

        $default_args = array(
            'post_type' => 'pcevent',
            'start' => '',
            'end' => '',
        );
        $args = wp_parse_args( $args, $default_args );

        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "
			SELECT wp_posts.ID
			FROM $wpdb->posts
			INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) 
			INNER JOIN $wpdb->postmeta AS mt1 ON ( $wpdb->posts.ID = mt1.post_id ) 
			WHERE 1=1
				AND ( ( $wpdb->postmeta.meta_key LIKE %s AND CAST($wpdb->postmeta.meta_value AS DATE) >= %s ) AND ( mt1.meta_key LIKE %s AND CAST(mt1.meta_value AS DATE) <= %s ) ) 
				AND $wpdb->posts.post_type = 'pcevent' 
				AND (($wpdb->posts.post_status = 'publish')) 
			GROUP BY $wpdb->posts.ID
			ORDER BY $wpdb->postmeta.meta_value ASC
		",
                '_start_date_%',
                $args['start'],
                '_end_date_%',
                $args['end']
            )
        );

        if ( empty( $post_ids ) ) {
            return;
        }

        $post_ids = apply_filters( 'pickle_calendar_get_events', $post_ids, $date );

        return $post_ids;
    }

    /**
     * Get the events dates.
     *
     * @access public
     * @param int $post_id (default: 0).
     * @return array
     */
    public function get_event_dates( $post_id = 0 ) {
        $dates = array();
        $meta = get_post_meta( $post_id );

        foreach ( $meta as $key => $value ) :
            if ( strpos( $key, '_start_date_' ) !== false ) :
                if ( isset( $value[0] ) ) :
                    preg_match( '/([0-9]+)/', $key, $matches );
                    $dates[ $matches[1] ]['start_date'] = $value[0];
                endif;
            elseif ( strpos( $key, '_end_date_' ) !== false ) :
                if ( isset( $value[0] ) ) :
                    preg_match( '/([0-9]+)/', $key, $matches );
                    $dates[ $matches[1] ]['end_date'] = $value[0];
                endif;
            endif;
        endforeach;

        return $dates;
    }

    /**
     * Filters function.
     *
     * @access public
     * @param string $args (default: '').
     * @return array
     */
    public function filters( $args = '' ) {
        $filters = array(
            'checkboxes' => array(),
            'tabs' => array(),
        );

        if ( ! isset( picklecalendar()->settings['taxonomies'] ) || empty( picklecalendar()->settings['taxonomies'] ) ) {
            return $filters;
        }

        foreach ( picklecalendar()->settings['taxonomies'] as $taxonomy ) :
            if ( ! isset( $taxonomy['display'] ) || ! $taxonomy['display'] ) {
                continue;
            }

            switch ( $taxonomy['display_type'] ) :
                case 'tabs':
                    $filters['tabs'][] = $this->filter_tab( $taxonomy['slug'], $taxonomy['label'], $taxonomy );
                    break;
                default:
                    $filters['checkboxes'][] = $this->filter_checkbox( $taxonomy['slug'], $taxonomy['label'] );
            endswitch;
        endforeach;

        return $filters;
    }

    /**
     * Filter checkbox.
     *
     * @access protected
     * @param string $slug (default: '').
     * @param string $label (default: '').
     * @return html
     */
    protected function filter_checkbox( $slug = '', $label = '' ) {
        $html = '';

        $terms = get_terms( array( 'taxonomy' => $slug ) );

        $html .= '<div class="pickle-calendar-filters filter-type-checkbox" data-filters="">';
            $html .= '<div class="filter ' . $slug . '">';
                $html .= '<div class="filter-label">' . ucwords( $label ) . '</div>';
        foreach ( $terms as $term ) :
            $html .= '<label for="filter-term-' . $term->term_id . '"><input type="checkbox" name="term[]" id="filter-term-' . $term->term_id . '" class="filter-term" value="' . $term->slug . '" /> ' . $term->name . '</label>';
                endforeach;
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Filter tab.
     *
     * @access protected
     * @param string $slug (default: '').
     * @param string $label (default: '').
     * @param array  $taxonomy (default: array()).
     * @return html
     */
    protected function filter_tab( $slug = '', $label = '', $taxonomy = array() ) {
        $html = '';

        $terms = get_terms( array( 'taxonomy' => $slug ) );

        $html .= '<div class="pickle-calendar-filters filter-type-tab" data-filters="">';
            $html .= '<div class="filter ' . $slug . '">';
                $html .= '<div class="filter-label">' . ucwords( $label ) . '</div>';
                $html .= '<ul class="filter-tabs">';
        if ( ! isset( $taxonomy['hide_all_tab'] ) || 1 != $taxonomy['hide_all_tab'] ) :
            $html .= '<li class="filter-tab active" data-tab-slug="all"><a href="#">All</a></li>';
                    endif;

        foreach ( $terms as $term ) :
            $html .= '<li class="filter-tab" data-tab-slug="' . $term->slug . '"><a href="#">' . $term->name . '</a></li>';
                    endforeach;
                $html .= '</ul>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Display filters.
     *
     * @access protected
     * @param array $filters (default: array()).
     * @return html
     */
    protected function filters_display( $filters = array() ) {
        $html = '';

        foreach ( $filters as $filter ) :
            $html .= $filter;
        endforeach;

        return $html;
    }

    /**
     * AJAX nav function.
     *
     * @access public
     */
    public function ajax_nav() {
        $month = ! empty( $_POST['month'] ) ? sanitize_text_field( wp_unslash( $_POST['month'] ) ) : '';
        $year = ! empty( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';

        $args = array(
            'month' => $month,
            'year' => $year,
            'echo' => false,
        );

        echo esc_attr( $this->calendar( $args ) );

        wp_die();
    }

    /**
     * Our shortcode.
     *
     * @access public
     * @param mixed $atts (array).
     * @return calendar
     */
    public function shortcode( $atts ) {
        $args = shortcode_atts(
            array(
                'show_filters' => true,
            ), $atts
        );

        $args['echo'] = false;

        return $this->calendar( $args );
    }

}
