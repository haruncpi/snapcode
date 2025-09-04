<?php
/**
 * Dumper class
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Classes;

/**
 * Dumper class
 */
class Dumper {

	/**
	 * Dump value.
	 *
	 * @param mixed $value value.
	 * @param int   $spaces spaces.
	 * @param int   $level level.
	 */
	public static function dump( $value, int $spaces = 2, int $level = 0 ): void {
		$indent = str_repeat( ' ', $spaces * $level );
		$pad    = str_repeat( ' ', $spaces );

        //phpcs:disabled
		switch ( gettype( $value ) ) {
			case 'array':
				if ( empty( $value ) ) {
					echo '[]';
					return;
				}
				echo "[\n";
				foreach ( $value as $key => $val ) {
					echo $indent . $pad . '"' . $key . '" => ';
					self::dump( $val, $spaces, $level + 1 );
					echo ",\n";
				}
				echo $indent . ']';
				break;

			case 'object':
				$id    = spl_object_id( $value );
				$props = get_object_vars( $value );
				if ( empty( $props ) ) {
					echo "{#{$id}}";
					return;
				}
				echo "{#{$id}\n";
				foreach ( $props as $k => $v ) {
					echo $indent . $pad . '+"' . $k . '": ';
					self::dump( $v, $spaces, $level + 1 );
					echo ",\n";
				}
				echo $indent . '}';
				break;

			case 'string':
				echo '"' . $value . '"';
				break;

			case 'boolean':
				echo $value ? 'true' : 'false';
				break;

			case 'NULL':
				echo 'null';
				break;

			default: // integer, double, resource, etc.
				echo $value;
		}
	}
    //phpcs:enabled
}
