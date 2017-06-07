{extends file="page.tpl"}

{block name="content"}

	<div class="container">
		<div class="page-header">
			<h1>Grading Analytics <small>{$departments['name']}</small></h1>
			<p>{$statistics[0]['timestamp']|date_format:'F j, Y'} Snapshot</p>
		</div>
	</div>

	<div class="container container-fluid">
		<table class="table table-hover table-striped table-condensed sortable">
			<thead>
				<tr>
					<th>Teacher(s)</th>
					<th>Course</th>
					<th>Students</th>
					<th>Turn-Around</th>
					<th class="sorting-placeholder" data-defaultsort="disabled"></th>
					<th>Lead Time</th>
					<th class="sorting-placeholder" data-defaultsort="disabled"></th>
					<th>Graded</th>
					<th class="sorting-placeholder" data-defaultsort="disabled"></th>
					<th>Due</th>
					<th>No Due Dates</th>
					<th class="sorting-placeholder" data-defaultsort="disabled"></th>
					<th>Retroactive Assignments</th>
					<th class="sorting-placeholder" data-defaultsort="disabled"></th>
					<th>Gradable Assignments</th>
					<th>Graded Assignments</th>
					<th data-dateformat="YYYY-MM-DD">Oldest Ungraded Assignment</th>
					<th class="sorting-placeholder" data-defaultsort="disabled"></th>
					<th>Zero-Point Assignments</th>
					<th class="sorting-placeholder" data-defaultsort="disabled"></th>
					<th colspan="2" data-defaultsort="disabled">Course Links</th>
				</tr>
			</thead>
			<tbody>
				{foreach $statistics as $statistic}
					<tr>
						<td>{implode("<br />", array_unique(unserialize($statistic['teacher[sortable_name]s'])))}</td>
						<td><a target="_blank" href="{$canvasInstanceUrl}/courses/{$statistic['course[id]']}">{implode("<br />", explode(": ",$statistic['course[name]']))}</a></td>
						<td>{$statistic['student_count']}</td>

						<td class="sorting-value">{if $statistic['graded_assignment_count'] > 0}{round($statistic['average_grading_turn_around'], 1)}{/if}</td>
						<td><span{if $statistic['graded_assignment_count'] > 0}{getLevel key='average_grading_turn_around' value=$statistic['average_grading_turn_around']}{else} class="warning level-3"{/if}>{if $statistic['graded_assignment_count'] > 0}{round($statistic['average_grading_turn_around'], 1)} days{else}No grades{/if}</span></td>

						<td class="sorting-value">{round($statistic['average_assignment_lead_time'], 1)}</td>
						<td><span{getLevel key='average_assignment_lead_time' value=round($statistic['average_assignment_lead_time'])}>{round($statistic['average_assignment_lead_time'], 1)} days</span></td>

						<td class="sorting-value">{round($statistic['average_submissions_graded']*100)}</td>
						<td><span{getLevel key='average_submissions_graded' value=$statistic['average_submissions_graded']}>{round($statistic['average_submissions_graded']*100)}%</span></td>
						<td>{$statistic['assignments_due_count']}</td>

						<td class="sorting-value">{$statistic['dateless_assignment_count']}</td>
						<td><span{getLevel key='dateless_assignment_count' value=round($statistic['dateless_assignment_count'], 1)}>{$statistic['dateless_assignment_count']}</span></td>

						<td class="sorting-value">{$statistic['created_after_due_count']}</td>
						<td><span{getLevel key='created_after_due_count' value=$statistic['created_after_due_count']}>{$statistic['created_after_due_count']}</span></td>
						<td><span{getLevel key='gradeable_assignment_count' value=$statistic['gradeable_assignment_count']}>{$statistic['gradeable_assignment_count']}</span></td>
						<td><span{getLevel key='graded_assignment_count' value=$statistic['graded_assignment_count']}>{$statistic['graded_assignment_count']}</span></td>
						<td class="sorting-value">{$statistic['oldest_ungraded_assignment_due_date']|date_format:'Y-m-d'}</td>
						<td>{if strlen($statistic['oldest_ungraded_assignment_name']) > 0}<a href="{$statistic['oldest_ungraded_assignment_url']}">{$statistic['oldest_ungraded_assignment_name']}</a><br /><small>(due {$statistic['oldest_ungraded_assignment_due_date']|date_format:'F j, Y'})</small>{else}-{/if}</td>

						<td class="sorting-value">{$statistic['zero_point_assignment_count']}</td>
						<td><span{getLevel key='zero_point_assignment_count' value=$statistic['zero_point_assignment_count']}>{$statistic['zero_point_assignment_count']}</span></td>
						<td><a class="btn btn-default" target="_blank" href="{$statistic['gradebook_url']}">Gradebook</a></td>
						{if !empty($statistic['analytics_page'])}<td><a class="btn btn-default" target="_blank" href="{$statistic['analytics_page']}">Grading Analytics</a></td>{/if}
					</tr>
				{/foreach}
			</body>
		</table>
	</div>

{/block}
