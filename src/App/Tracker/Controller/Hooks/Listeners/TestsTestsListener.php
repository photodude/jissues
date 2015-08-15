<?php
/**
 * Part of the Joomla Tracker's Tracker Application
 *
 * @copyright  Copyright (C) 2015 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace App\Tracker\Controller\Hooks\Listeners;

use App\Projects\TrackerProject;

use Joomla\Event\Event;

use JTracker\Github\DataType\Commit\Status;

/**
 * Event listener for the joomla-cms items events.
 *
 * @since  1.0
 */
class TestsTestsListener extends AbstractTestsListener
{
	/**
	 * Event for after issues are created in the application
	 *
	 * @param   Event  $event  Event object
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onTestAfterSubmit(Event $event)
	{
		// Pull the arguments array
		$arguments = $event->getArguments();

		$status = $this->getStatus($arguments['project'], $arguments['data']->testsSuccess, $arguments['data']->testsFailure, $arguments['issueNumber']);

		$this->createStatus($arguments['github'], $arguments['project'], $arguments['issueNumber'], $status);
	}

	/**
	 * Get a GitHub Status object.
	 *
	 * @param   TrackerProject  $project       The project object.
	 * @param   array           $testsSuccess  Successful tests.
	 * @param   array           $testsFailure  Failed tests.
	 * @param   integer         $issueNumber   The issue number.
	 *
	 * @since   1.0
	 * @return  Status
	 */
	private function getStatus(TrackerProject $project, array $testsSuccess, array $testsFailure, $issueNumber)
	{
		$status = new Status;

		$successes = count($testsSuccess);
		$failures = count($testsFailure);

		if ($successes >= 2 && $failures == 0)
		{
			// 2 or more successes and 0 failures.
			$status->state = 'success';
		}
		elseif ($successes == 0 && $failures >= 2)
		{
			// 0 successes and 2 or more failures.
			$status->state = 'failure';
		}
		else
		{
			// Everything else.
			$status->state = 'pending';
		}

		$status->description = sprintf(
			'Human Test Results: %1$d Successful %2$d Failed.', $successes, $failures
		);

		$status->context = 'JTracker/HumanTestResults';

		// @todo - where to get the URL from a CLI script? - create a config setting??
		$targetBaseUrl = 'http://issues.joomla.org';

		$status->targetUrl = $targetBaseUrl . '/tracker/' . $project->alias . '/' . $issueNumber;

		return $status;
	}
}
