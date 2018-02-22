<?php

class wsa_sensei_stuff {

    public function sensei_active_courses($chart_content, $title, $colors, $legend, $is_3d) {

        global $woothemes_sensei;

        $args = array(
            'posts_per_page'   => -1,
            'post_status'      => 'publish',
            'post_type'        => 'course',
            'suppress_filters' => true
        );
        $posts_array = get_posts( $args );

        $args = array( 'posts_per_page' => 5, 'post_type'=> 'course', 'posts_per_page' => -1 );

        $myposts = get_posts( $args ); ?>

        <script type="text/javascript">
            var person = [
                <?php
                echo "['Task1', 'Hours per Day'],";
                foreach ( $myposts as $post ) : setup_postdata( $post );
                    $activity_args = array(
                        'post_id' => $post->ID,
                        'type' => 'sensei_course_status',
                        'count' => true,
                        'number' => 0,
                        'offset' => 0,
                        'status' => 'any',
                    );
                    $course_learners = WooThemes_Sensei_Utils::sensei_check_for_activity($activity_args, false);
                    echo  "['$post->post_title', { v:$course_learners, f: 'Total Enrolled Students: '+$course_learners}],";
                endforeach;
                wp_reset_postdata();

                ?>];
            wsa_create_chart(person,'<?php echo $chart_content?>', '<?php echo $title?>', <?php echo $colors ?> , '<?php echo $legend ?>', '<?php echo $is_3d ?>'); // Display Chart
        </script>
        <?php
    }

    public function sensei_complete_courses($chart_content, $title, $colors, $legend, $is_3d) {

        global $woothemes_sensei,$wpdb;
        global $wpdb;
        // $prefix =  $wpdb->prefix; wp_postmeta
        $wp_comments = $wpdb->prefix.'comments';
        //print_r($wp_comments);
        $wp_users = $wpdb->prefix.'users';
        $wp_postmeta = $wpdb->prefix.'postmeta';


        $complete_courses = $wpdb->get_results( "SELECT DISTINCT (`comment_post_ID`) FROM $wp_comments WHERE `comment_type` = 'sensei_course_status' and `comment_approved` = 'complete'", OBJECT ); ?>

        <script type="text/javascript">
            var person = [
                <?php
                echo "['Task2', 'Hou2rs per Day'],";
                foreach ( $complete_courses as $complete_course ) : setup_postdata( $complete_course );
                    $course_title = get_the_title( $complete_course->comment_post_ID );
                    $learners_counts = $wpdb->get_results( "SELECT count(`user_id`) as 'user_id' FROM $wp_comments WHERE `comment_type` = 'sensei_course_status' and `comment_approved` = 'complete' and `comment_post_ID` = $complete_course->comment_post_ID", OBJECT );
                    foreach($learners_counts as $learners_count) {
                        $learners = $learners_count->user_id;
                        echo  "['$course_title',{ v: $learners, f: 'Total Learners: $learners' }],";
                    }
                endforeach;
                wp_reset_postdata();
                ?>];
            wsa_create_chart(person,'<?php echo $chart_content?>', '<?php echo $title?>', <?php echo $colors ?> , '<?php echo $legend ?>', '<?php echo $is_3d ?>'); // Display Chart
        </script>
        <?php
    }

    public function sensei_lesson_graded($chart_content, $title, $colors, $legend, $is_3d) {

        global $woothemes_sensei,$wpdb;
        $wp_comments = $wpdb->prefix.'comments';

        $wp_users = $wpdb->prefix.'users';
        $wp_postmeta = $wpdb->prefix.'postmeta';
        $lesson_count_graded = array();
        $lesson_count_ungraded = array();
        $lesson_ungraded = $wpdb->get_results( "SELECT count(`comment_post_ID`) as 'total_ungraded', `comment_post_ID` FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'ungraded'", OBJECT );
        $lesson_graded = $wpdb->get_results( "SELECT count(`comment_post_ID`) as `total_graded` FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'passed' or `comment_approved` = 'graded'", OBJECT ); ?>

        <script type="text/javascript">
            var person = [
            <?php
            echo "['Task2', 'Hou2rs per Day'],";
            $counter_un = 1;
            foreach ( $lesson_ungraded as $lesson_ungrade ) : setup_postdata( $lesson_ungrade );
            $lesson_ungraded_names = $wpdb->get_results( "SELECT `comment_post_ID` FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'ungraded'", OBJECT );
            echo  "['Un Graded', {      v: $lesson_ungrade->total_ungraded, f: \n'Lessons:";
            foreach($lesson_ungraded_names as $lesson_ungraded_name) { ?> \n <?php
            $lesson_counter = $wpdb->get_results( "SELECT count(`comment_post_ID`) as 'lesson_ungraded_count' FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'ungraded' and comment_post_ID = $lesson_ungraded_name->comment_post_ID", OBJECT );
            foreach( $lesson_counter as $lesson_count ) {
                $lesson_count_ungraded[] = $lesson_count->lesson_ungraded_count;
            }

            echo " ".$counter_un++.'. '.get_the_title( $lesson_ungraded_name->comment_post_ID ) . ' ('.$lesson_count_ungraded[$counter_un-2] .')' ."";
            }
            echo  "' } ],";

            endforeach;
            $counter = 1;
            foreach ( $lesson_graded as $lesson_grade ) : setup_postdata( $lesson_grade );
            $lesson_graded_names = $wpdb->get_results( "SELECT `comment_post_ID` FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'passed'", OBJECT );
            echo  "['Graded', {      v: $lesson_grade->total_graded, f: \n'Lessons:";
            foreach($lesson_graded_names as $lesson_graded_name) { ?> \n <?php
            $lesson_counter = $wpdb->get_results( "SELECT count(`comment_post_ID`) as 'lesson_graded_count' FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'passed' and comment_post_ID = $lesson_graded_name->comment_post_ID", OBJECT );
            foreach( $lesson_counter as $lesson_count ) {
                $lesson_count_graded[] = $lesson_count->lesson_graded_count;
            }

            echo " ".$counter++.'. '.get_the_title( $lesson_graded_name->comment_post_ID ) . ' ('.$lesson_count_graded[$counter-2] .')' ."";
            }
            echo  "' } ],";
            endforeach;
            wp_reset_postdata();
            ?>];
            wsa_create_chart(person,'<?php echo $chart_content?>', '<?php echo $title?>', <?php echo $colors ?> , '<?php echo $legend ?>', '<?php echo $is_3d ?>'); // Display Chart
        </script>
        <?php
    }

    public function sensei_enrolled_user_by_month($chart_content, $title, $colors) {

        global $wpdb;
        $wp_comments = $wpdb->prefix.'comments';
        $wp_users = $wpdb->prefix.'users';
        $wp_postmeta = $wpdb->prefix.'postmeta';
        $user_registration = $wpdb->get_results( "SELECT DISTINCT(DATE(user_registered)) as 'user_registered' FROM $wp_users order by `user_registered` ASC", OBJECT );
        $cc = 0;
        ?>
        <script>
            var line_chart = [<?php
                foreach($user_registration as $user_register) {
                $date_search = $user_register->user_registered;
                $date_conv = strtotime($user_register->user_registered);
                $date = date('m/d/Y', $date_conv);
                $month = date('m', $date_conv);
                $year = date('Y', $date_conv);
                $enrolled_count = $wpdb->get_results( "SELECT count(`user_registered`) as 'user_registered_count' FROM $wp_users WHERE `user_registered` like '%$month%'", OBJECT );
                $enrolled_count = $wpdb->get_results( "SELECT count(`user_registered`) as 'user_registered_count' FROM $wp_users WHERE `user_registered` like '%$month%'", OBJECT );

                foreach($enrolled_count as $enrolled_counts) {
                ?> [new Date(<?php echo $year?>, <?php echo $month ?> ), <?php echo $enrolled_counts->user_registered_count ?>,'Test'],<?php
                $cc++;
                }
                }
                ?>]
        </script>

        <script type="text/javascript">
            wsa_create_line_chart(line_chart,'<?php echo $chart_content?>', '<?php echo $title?>', <?php echo $colors ?>); // Display Chart
        </script>
        <?php
    }

    public function sensei_failed_courses($chart_content, $title, $colors, $legend, $is_3d) {

        global $wpdb;
        $wp_comments = $wpdb->prefix.'comments';
        $wp_users = $wpdb->prefix.'users';
        $wp_postmeta = $wpdb->prefix.'postmeta';
        $user_failed_courses = $wpdb->get_results( "SELECT DISTINCT(`comment_post_ID`) FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'failed'", OBJECT );
        $cc = 0;
        ?>
        <script>
            var users = [
                <?php
                echo "['Task2', 'Hou2rs per Day'],";
                foreach($user_failed_courses as $user_failed) {
                $lesson_ID = $user_failed->comment_post_ID;

                $failed_users = $wpdb->get_results( "SELECT count(`user_id`) as 'users' FROM $wp_comments WHERE `comment_type` = 'sensei_lesson_status' and `comment_approved` = 'failed' and `comment_post_ID` = $lesson_ID", OBJECT );

                $CourseTitle = get_post_meta($lesson_ID,'_lesson_course',true);

                foreach($failed_users as $users) {
                ?>
                ['<?php echo get_the_title( $lesson_ID )?>',{ v: <?php echo $users->users ?>, f: 'Course: <?php echo get_the_title( $CourseTitle) ?> \n <?php echo "Total Learners: ".$users->users ?>\n' } ],
                <?php
                }
                }
                ?>
            ]
        </script>

        <script type="text/javascript">
            wsa_create_chart(users, '<?php echo $chart_content?>', '<?php echo $title?>', <?php echo $colors ?> , '<?php echo $legend ?>', '<?php echo $is_3d ?>'); // Display Chart
        </script>
        <?php
    }

    public function sensei_most_sell_courses($chart_content, $title, $colors, $legend, $is_3d) {
        global $wpdb;
        $wp_comments = $wpdb->prefix.'comments';
        $wp_users = $wpdb->prefix.'users';
        $wp_postmeta = $wpdb->prefix.'postmeta';
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_key' => 'total_sales',
            'meta_value' => 0,
            'meta_compare' => '>',
            'orderby' => 'meta_value_num',
        );
        $most_sell_courses = $wpdb->get_results( "SELECT `meta_value`,`post_id` FROM $wp_postmeta WHERE `meta_key` LIKE '%total_sales%' and meta_value > 0", OBJECT );
        ?>
        <script>
            var users = [
                <?php
                echo "['Task2', 'Hou2rs per Day'],";

                foreach($most_sell_courses as $most_sell) { ?>
                ['<?php echo get_the_title( $most_sell->post_id )?>', { v: <?php echo $most_sell->meta_value ?> , f: 'Total Sell: <?php echo $most_sell->meta_value ?>' }],
                <?php } ?>

            ]
            wsa_create_chart(users, '<?php echo $chart_content?>', '<?php echo $title?>', <?php echo $colors ?> , '<?php echo $legend ?>', '<?php echo $is_3d ?>'); // Display Chart
        </script>
        <?php
    }
}