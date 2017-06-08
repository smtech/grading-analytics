#!/usr/bin/env php
<?php

define('IGNORE_UI', true);

require_once __DIR__ . '/../common.inc.php';
require_once __DIR__ . '/../constants.inc.php';

use smtech\GradingAnalytics\Toolbox;
use smtech\CanvasPest\CanvasPest;

// http://stackoverflow.com/a/21896310
function hoursRange($lower = 0, $upper = 86400, $step = 3600, $keyFormat = '', $value = '', $valueIsFormat = false)
{
    $times = array();

    if (empty( $value ) && $valueIsFormat) {
        $value = 'g:i a';
    }

    if (empty($keyFormat)) {
        $keyFormat = 'g:i a';
    }

    foreach (range( $lower, $upper, $step ) as $increment) {
        $increment = gmdate( $keyFormat, $increment );

        list( $hour, $minutes ) = explode( ':', $increment );

        $date = new DateTime( $hour . ':' . $minutes );

        $times[(string) $increment] = ($valueIsFormat ? $date->format( $value ) : $value);
    }

    return $times;
}

function collectStatistics($term, Toolbox $toolbox)
{
    $courses = $toolbox->api_get(
        '/accounts/' . $toolbox->config(Toolbox::TOOL_CANVAS_ACCOUNT_ID) . '/courses',
        array(
            'with_enrollments' => 'true',
            'enrollment_term_id' => $term
        )
    );

    // so that everything has a consistent benchmark
    $timestamp = time();

    foreach ($courses as $course) {
        $statistic = array(
            'timestamp' => date(DATE_ISO8601, $timestamp),
            'course[id]' => $course['id'],
            'course[name]' => $course['name'],
            'course[account_id]' => $course['account_id'],
            'gradebook_url' => $_SESSION[CANVAS_INSTANCE_URL] . "/courses/{$course['id']}/gradebook2",
            'assignments_due_count' => 0,
            'dateless_assignment_count' => 0,
            'created_after_due_count' => 0,
            'gradeable_assignment_count' => 0,
            'graded_assignment_count' => 0,
            'zero_point_assignment_count' => 0,
            'analytics_page' => $_SESSION[CANVAS_INSTANCE_URL] . "/courses/{$course['id']}/external_tools/" . $toolbox->config(Toolbox::TOOL_CANVAS_EXTERNAL_TOOL_ID)
        );

        $teacherIds = array();
        $teacherNames = array();
        $teachers = $toolbox->api_get(
            "/courses/{$course['id']}/enrollments",
            array(
                'type[]' => 'TeacherEnrollment'
            )
        );
        foreach ($teachers as $teacher) {
            $teacherIds[] = $teacher['user']['id'];
            $teacherNames[] = $teacher['user']['sortable_name'];
        }
        $statistic['teacher[id]s'] = serialize($teacherIds);
        $statistic['teacher[sortable_name]s'] = serialize($teacherNames);

        $account = $toolbox->api_get("/accounts/{$course['account_id']}");
        $statistic['account[name]'] = $account['name'];

        // ignore classes with no teachers (how do they even exist? weird.)
        if (count($teacherIds) != 0) {
            $statistic['student_count'] = 0;
            $students = $toolbox->api_get(
                "/courses/{$course['id']}/enrollments",
                array(
                    'type[]' => 'StudentEnrollment'
                )
            );
            $statistic['student_count'] = $students->count();

            // ignore classes with no students
            if ($statistic['student_count'] != 0) {
                $assignments = $toolbox->api_get(
                    "/courses/{$course['id']}/assignments"
                );

                $gradedSubmissionsCount = 0;
                $turnAroundTimeTally = 0;
                $leadTimeTally = 0;
                $createdModifiedHistogram = array(
                    HISTOGRAM_CREATED => hoursRange(0, 86400, 3600, '', 0),
                    HISTOGRAM_MODIFIED => hoursRange(0, 86400, 3600, '', 0)
                );

                foreach ($assignments as $assignment) {
                    // ignore unpublished assignments
                    if ($assignment['published'] == true) {
                        // check for due dates
                        $dueDate = new DateTime($assignment['due_at']);
                        $dueDate->setTimeZone(new DateTimeZone(SCHOOL_TIME_ZONE));
                        if (($timestamp - $dueDate->getTimestamp()) > 0) {
                            $statistic['assignments_due_count']++;

                            // update created_modified_histogram
                            $createdAt = new DateTime($assignment['created_at']);
                            $createdAt->setTimeZone(new DateTimeZone(SCHOOL_TIME_ZONE));
                            $updatedAt = new DateTime($assignment['updated_at']);
                            $updatedAt->setTimeZone(new DateTimeZone(SCHOOL_TIME_ZONE));
                            $createdModifiedHistogram[HISTOGRAM_CREATED][$createdAt->format('g:00 a')]++;
                            if ($createdAt != $updatedAt) {
                                $createdModifiedHistogram[HISTOGRAM_MODIFIED][$updatedAt->format('g:00 a')]++;
                            }

                            // tally lead time on the assignment
                            $leadTimeTally += strtotime($assignment['due_at']) - strtotime($assignment['created_at']);

                            // was the assignment created after it was due?
                            if (strtotime($assignment['due_at']) < strtotime($assignment['created_at'])) {
                                $statistic['created_after_due_count']++;
                            }

                            // ignore ungraded assignments
                            if ($assignment['grading_type'] != 'not_graded') {
                                $statistic['gradeable_assignment_count']++;
                                $hasBeenGraded = false;

                                // tally zero point assignments
                                if ($assignment['points_possible'] == '0') {
                                    $statistic['zero_point_assignment_count']++;
                                }

                                // build submission statistic
                                $submissions = $toolbox->api_get(
                                    "/courses/{$course['id']}/assignments/{$assignment['id']}/submissions"
                                );
                                foreach ($submissions as $submission) {
                                    if ($submission['workflow_state'] == 'graded') {
                                        if ($hasBeenGraded == false) {
                                            $hasBeenGraded = true;
                                            $statistic['graded_assignment_count']++;
                                        }
                                        $gradedSubmissionsCount++;
                                        $turnAroundTimeTally += max(
                                            0,
                                            strtotime($submission['graded_at']) - strtotime($assignment['due_at'])
                                        );
                                    }
                                }

                                if (!$hasBeenGraded) {
                                    if (array_key_exists('oldest_ungraded_assignment_due_date', $statistic)) {
                                        if (strtotime($assignment['due_at']) < strtotime($statistic['oldest_ungraded_assignment_due_date'])) {
                                            $statistic['oldest_ungraded_assignment_due_date'] = $assignment['due_at'];
                                            $statistic['oldest_ungraded_assignment_url'] = $assignment['html_url'];
                                            $statistic['oldest_ungraded_assignment_name'] = $assignment['name'];
                                        }
                                    } else {
                                        $statistic['oldest_ungraded_assignment_due_date'] = $assignment['due_at'];
                                        $statistic['oldest_ungraded_assignment_url'] = $assignment['html_url'];
                                        $statistic['oldest_ungraded_assignment_name'] = $assignment['name'];
                                    }
                                }
                            }
                        } else {
                            $statistic['dateless_assignment_count']++;
                        }
                    }
                }

                $statistic['created_modified_histogram'] = serialize($createdModifiedHistogram);

                // calculate average submissions graded per assignment (if non-zero)
                if ($statistic['gradeable_assignment_count'] && $statistic['student_count']) {
                    $statistic['average_submissions_graded'] = $gradedSubmissionsCount / ($statistic['gradeable_assignment_count'] * $statistic['student_count']);
                }

                // calculate the average lead-time on assignments
                if ($statistic['assignments_due_count']) {
                    $statistic['average_assignment_lead_time'] = $leadTimeTally / $statistic['assignments_due_count'] / 60 / 60 / 24;
                }

                // calculate average grading turn-around per submission
                if ($gradedSubmissionsCount) {
                    $statistic['average_grading_turn_around'] = $turnAroundTimeTally / $gradedSubmissionsCount / 60 / 60 / 24;
                }

                $query = "INSERT INTO `course_statistics`";
                $fields = array();
                $values = array();
                while (list($field, $value) = each($statistic)) {
                    $fields[] = $field;
                    $values[] = $value;
                }
                $query .= ' (`' . implode('`, `', $fields) . "`) VALUES ('" . implode("', '", $values) . "')";
                $result = $toolbox->mysql_query($query);
            }
        }
    }
}

/* force API configuration from config file */
$toolbox->setApi(new CanvasPest(
    $_SESSION[CANVAS_INSTANCE_URL] . '/api/v1',
    $toolbox->config(Toolbox::TOOL_CANVAS_API)['token']
));

/* collect data on terms currently in session */
try {
    $terms = $toolbox->api_get('accounts/1/terms');
    $now = strtotime('now');
    foreach ($terms['enrollment_terms'] as $term) {
        if (isset($term['start_at']) && isset($term['end_at'])) {
            if ((strtotime($term['start_at']) <= $now) && ($now <= strtotime($term['end_at']))) {
                collectStatistics($term['id'], $toolbox);
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
