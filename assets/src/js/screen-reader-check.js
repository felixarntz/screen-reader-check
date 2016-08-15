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
