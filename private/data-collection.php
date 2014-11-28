<?php
require_once(__DIR__ . '/../smcanvaslib/config.inc.php');
require_once(__DIR__ . '/../config.inc.php');
require_once(__DIR__ . '/../.ignore.grading-analytics-authentication.inc.php');
require_once(SMCANVASLIB_PATH . '/include/debug.inc.php');
define('DEBUGGING', DEBUGGING_LOG);
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/mysql.inc.php');

// http://stackoverflow.com/a/21896310
function hoursRange( $lower = 0, $upper = 86400, $step = 3600, $keyFormat = '' , $value = '', $valueIsFormat = false) {
    $times = array();

    if ( empty( $value ) && $valueIsFormat) {
        $value = 'g:i a';
    }
    
    if ( empty($keyFormat)) {
	    $keyFormat = 'g:i a';
    }

    foreach ( range( $lower, $upper, $step ) as $increment ) {
        $increment = gmdate( $keyFormat, $increment );

        list( $hour, $minutes ) = explode( ':', $increment );

        $date = new DateTime( $hour . ':' . $minutes );

        $times[(string) $increment] = ($valueIsFormat ? $date->format( $value ) : $value);
    }

    return $times;
}

function collectStatistics($term) {
	$coursesApi = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);
	$assignmentsApi = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);
	$lookupApi = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);
	
	// TODO make this configurable
	$courses = $coursesApi->get(
		'/accounts/132/courses',
		array(
			'with_enrollments' => 'true',
			'enrollment_term_id' => $term
		)
	);
	
	// so that everything has a consistent benchmark
	$timestamp = time();
	
	do {
		foreach ($courses as $course) {
			$statistic = array(
				'timestamp' => date(DATE_ISO8601, $timestamp),
				'course[id]' => $course['id'],
				'course[name]' => $course['name'],
				'course[account_id]' => $course['account_id'],
				'gradebook_url' => 'https://' . parse_url(CANVAS_API_URL, PHP_URL_HOST) . "/courses/{$course['id']}/gradebook2",
				'assignments_due_count' => 0,
				'dateless_assignment_count' => 0,
				'created_after_due_count' => 0,
				'gradeable_assignment_count' => 0,
				'graded_assignment_count' => 0,
				'zero_point_assignment_count' => 0,
				'analytics_page' => APP_URL . "/course-summary.php?course_id={$course['id']}"
			);
		
			$teacherIds = array();
			$teacherNames = array();
			$teachers = $lookupApi->get(
				"/courses/{$course['id']}/enrollments",
				array(
					'type[]' => 'TeacherEnrollment'
				)
			);
			do {
				foreach ($teachers as $teacher) {
					$teacherIds[] = $teacher['user']['id'];
					$teacherNames[] = $teacher['user']['sortable_name'];
				}
			} while ($teachers = $lookupApi->nextPage());
			$statistic['teacher[id]s'] = serialize($teacherIds);
			$statistic['teacher[sortable_name]s'] = serialize($teacherNames);
			
			$account = $lookupApi->get("/accounts/{$course['account_id']}");
			$statistic['account[name]'] = $account['name'];

			// ignore classes with no teachers (how do they even exist? weird.)
			if (count($teacherIds) != 0) {
				$statistic['student_count'] = 0;
				$students = $lookupApi->get(
					"/courses/{$course['id']}/enrollments",
					array(
						'type[]' => 'StudentEnrollment'
					)
				);
				do {
					$statistic['student_count'] += count($students);
				} while ($students = $lookupApi->nextPage());
				
				// ignore classes with no students
				if ($statistic['student_count'] != 0) {
					$assignments = $assignmentsApi->get(
						"/courses/{$course['id']}/assignments"
					);

					$gradedSubmissionsCount = 0;
					$turnAroundTimeTally = 0;
					$leadTimeTally = 0;
					$createdModifiedHistogram = array(
						HISTOGRAM_CREATED => hoursRange(0, 86400, 3600, '', 0),
						HISTOGRAM_MODIFIED => hoursRange(0, 86400, 3600, '', 0)
					);
					do {
						
						foreach ($assignments as $assignment) {
							
							// ignore unpublished assignments
							if ($assignment['published'] == true) {
								
								// check for due dates
								$dueDate = new DateTime($assignment['due_at']);
								if (($timestamp - $dueDate->getTimestamp()) > 0) {
									$statistic['assignments_due_count']++;
									
									// update created_modified_histogram
									$createdModifiedHistogram[HISTOGRAM_CREATED][date("g:00 a", strtotime($assignment['created_at']))]++;
									$createdModifiedHistogram[HISTOGRAM_MODIFIED][date("g:00 a", strtotime($assignment['due_at']))]++;
									
									// tally lead time on the assignment
									$leadTimeTally += strtotime($assignment['due_at']) - strtotime($assignment['created_at']);
									
									// was the assignment created after it was due?
									if (strtotime($assignment['due_at']) < strtotime($assignment['created_at'])) {
										$statistic['created_after_due_count']++;
									}
									
									// ignore ungraded assignments
									if ($assignment['grading_type'] != 'not_graded')
									{
										$statistic['gradeable_assignment_count']++;
										$hasBeenGraded = false;
										
										// ignore (but tally) zero point assignments
										if ($assignment['points_possible'] == '0') {
											$statistic['zero_point_assignment_count']++;
										} else {
											$submissions = $lookupApi->get(
												"/courses/{$course['id']}/assignments/{$assignment['id']}/submissions"
											);
											do {
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
											} while ($submissions = $lookupApi->nextPage());
											
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
									}
								} else {
									$statistic['dateless_assignment_count']++;
								}
							}
						}
					} while ($assignments = $assignmentsApi->nextPage());
					
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
					$result = mysqlQuery($query);
					/* displayError(
						array(
							'gradedSubmissionsCount' => $gradedSubmissionsCount,
							'turnAroundTimeTally' => $turnAroundTimeTally,
							'statistic' => $statistic,
							'query' => $query,
							'result' => $result
						),
						true
					); */
				}
			}
		}
	} while ($courses = $coursesApi->nextPage());
}

debugFlag('START');

/* collect data on terms currently in session */
$api = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);
$terms = $api->get('accounts/1/terms');
$now = strtotime('now');
do {
	foreach ($terms['enrollment_terms'] as $term) {
		if (isset($term['start_at']) && isset($term['end_at'])) {
			if ((strtotime($term['start_at']) <= $now) && ($now <= strtotime($term['end_at']))) {
				collectStatistics($term['id']);
			}
		}
	}
} while ($terms = $api->nextPage());

/* check to see if this data collection has been scheduled. If it hasn't,
   schedule it to run nightly. */
/* thank you http://stackoverflow.com/a/4421284 ! */
$crontab = DATA_COLLECTION_CRONTAB . ' ' . realpath('.') . '/data-collection.sh';
$crontabs = shell_exec('crontab -l');
if (strpos($crontabs, $crontab) === false) {
	$filename = md5(time()) . '.txt';
	file_put_contents("/tmp/$filename", $crontabs . $crontab . PHP_EOL);
	shell_exec("crontab /tmp/$filename");
	debugFlag("added new scheduled data-collection to crontab");
}

debugFlag('FINISH');

?>