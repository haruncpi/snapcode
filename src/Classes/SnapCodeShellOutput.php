<?php
/**
 * SnapCodeShellOutput.php
 *
 * @package SnapCode
 */

namespace SnapCode\Classes;

/**
 * Class SnapCodeShellOutput
 */
class SnapCodeShellOutput extends \Psy\Output\ShellOutput {
	/**
	 * Output
	 *
	 * @override
	 *
	 * @var null|string
	 */
	public $output = null;

	/**
	 * Holds exceptions
	 *
	 * @override
	 *
	 * @var null|\Exception
	 */
	public $exception = null;

	/**
	 * Write a message to the output.
	 *
	 * @override
	 *
	 * @param string $message A message to write to the output.
	 * @param bool   $newline Whether to add a newline or not.
	 *
	 * @return void
	 */
	public function doWrite( $message, $newline ): void {
		$this->output .= $message;
	}
}
