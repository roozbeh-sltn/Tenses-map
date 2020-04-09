// Voice start and stop
(function($) {
	//error message hide
	$(".tts_container .ttsw_text").live("change, keypress", function(){	
		if( $(".ttsw_msg .error").length ) {
			$(".ttsw_msg" ).html("");
		}
	});
	//Start voice playing
	$(".tts_container .play").live("click", function(){	
		var container = $(this).parent( ".tts_container" );
		var speak_words =  $(".ttsw_text", container).val();
		var voice =  $(".ttsw_voice", container).val();
		if( speak_words != '' ) {
			responsiveVoice.speak( speak_words, voice);
			if(responsiveVoice.isPlaying()) {
			  console.log("I hope you are listening");
			}
		} else {
			$(".ttsw_msg", container).html('<div class="error">Please write text. Text should not be empty.</div>');
		}
	});
	//Stop voice playing
	$(".tts_container .stop").live("click", function(){	
		if(responsiveVoice.isPlaying()) {
			responsiveVoice.cancel();
		}
	});
})( jQuery );