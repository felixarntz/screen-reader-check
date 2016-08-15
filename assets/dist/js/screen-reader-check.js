/*!
 * Screen Reader Check (https://screen-reader-check.felix-arntz.me)
 * By Felix Arntz (https://leaves-and-love.net)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
(function ( exports, wp, $ ) {

	if ( ! exports.nonces ) {
		return;
	}

	exports.ajax = function( action, data ) {
		if ( exports.nonces[ action ] ) {
			data.nonce = exports.nonces[ action ];
		}

		return wp.ajax.post( 'src_' + action, data );
	};

	exports.createCheck = function( args ) {
		return exports.ajax( 'create_check', args );
	};

	exports.runNextTest = function( check_id, args ) {
		args.check_id = check_id;

		return exports.ajax( 'run_next_test', args );
	};

}( window.screenReaderCheck || {}, wp, jQuery ) );
