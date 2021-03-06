/* global wq_l10n, alert */
/*
 * WP Quiz Pro Plugin by MyThemeShop
 * https://mythemeshop.com/plugins/wp-quiz-pro/
*/

/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.throttle=a=function(e,f,j,i){var h,d=0;if(typeof f!=="boolean"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};$.debounce=function(d,e,f){return f===c?a(d,e,false):a(d,f,e!==false)}})(this);

;( function( $ ) {

	'use strict';

	// Move or create fb-root
	function moveFbRoot() {
		var fbRoot = $( '#fb-root' );
		if ( fbRoot.length > 0 ) {
			$( 'body' ).prepend( fbRoot );
		} else {
			$( 'body' ).prepend( '<div id="fb-root"/>' );
		}
	}

	moveFbRoot();

	if ( 'function' === typeof $.fn.flip ) {
		$( '.wq_IsFlip .card' ).flip();
	}

	if ( 'function' === typeof $.fn.jTinder ) {
		$( '.wq_IsSwiper' ).jTinder({
			infinite: false,
			onDislike: function( item, element ) {

				var id = $( item ).data( 'slideid' ),
					resItem		= $( element ).closest( '.wq_quizCtr' ).find( '.wq_singleResultWrapper' ).find( '#result-' + id );

				resItem.data( 'result', 0 );
				resItem.find( '.resultContent' ).append( '<div class="userVote negativeVote">' + wq_l10n.youVoted + ' <i class="sprite sprite-times"></i></div>' );
				var count = resItem.find( '#downVote' ).text();
				if ( -1 === count.indexOf( 'K' ) ) {
					var newCount = parseInt( count ) + 1;
					resItem.find( '#downVote' ).text( newCount );
				}
			},
			onLike: function( item, element ) {

				var id = $( item ).data( 'slideid' ),
					resItem		= $( element ).closest( '.wq_quizCtr' ).find( '.wq_singleResultWrapper' ).find( '#result-' + id );

				resItem.data( 'result', 1 );
				resItem.find( '.resultContent' ).append( '<div class="userVote positiveVote">' + wq_l10n.youVoted + ' <i class="sprite sprite-check"></i></div>' );
				var count = resItem.find( '#upVote' ).text();
				if ( -1 === count.indexOf( 'K' ) ) {
					var newCount = parseInt( count ) + 1;
					resItem.find( '#upVote' ).text( newCount );
				}
			},
			onEndStack: function( instance, element ) {

				var quizElem		= $( element ).closest( '.wq_quizCtr' ),
					isRetakeable	= parseInt( quizElem.data( 'retake-quiz' ) ),
					allQElem		= quizElem.find( '.wq_questionsCtr' ),
					resElem			= quizElem.find( '.wq_singleResultWrapper' ),
					resultsObj		= {};

				resElem.find( '.resultItem' ).each(function() {

					var $this = $( this );

					resultsObj[ $this.data( 'uid' ) ] = $this.data( 'result' );
				});

				$.post( quizElem.data( 'ajax-url' ), {
					action: 'wq_quizResults',
					pid: quizElem.data( 'quiz-pid' ),
					results: resultsObj,
					type: 'swiper',
					_nonce: wq_l10n.nonce
				});

				allQElem.fadeOut( 'slow' );

				setTimeout(function() {

					resElem.fadeIn( 'slow' );

					if ( isRetakeable ) {
						quizElem.find( '.wq_retakeSwiperBtn' ).show();
					}
				}, 900 );

				$( '.wq_retakeSwiperBtn' ).on( 'click', function() {

					instance.restart();
					resElem.find( '.userVote' ).remove();
					resElem.fadeOut( 'slow' );

					setTimeout(function() {
						quizElem.find( '.wq_retakeSwiperBtn' ).hide();
						allQElem.fadeIn( 'slow' );
					}, 900 );
				});
			}
		});

		$( '.actions .like, .actions .dislike' ).click(function( event ) {

			event.preventDefault();

			$( '.wq_IsSwiper' ).jTinder( $( this ).attr( 'class' ) );
		});
	}

	$( document ).on( 'click', '.wq_beginQuizSwiperCtr', function( event ) {

		event.preventDefault();

		var quizElem = $( this ).closest( '.wq_quizCtr' );
		quizElem.find( '.wq_swiperQuizPreviewInfoCtr' ).fadeOut( 'slow' );
		quizElem.find( '.wq_QuestionWrapperSwiper' ).fadeIn( 'slow' );
	});

	$( document ).on( 'click', '.wq_btn-continue', $.debounce( 350, function( event ) {

		event.preventDefault();

		var quizElem			= $( this ).closest( '.wq_quizCtr' ),
			curQ				= parseInt( quizElem.data( 'current-question' ) ),
			curQElem			= quizElem.find( '.wq_questionsCtr > .wq_singleQuestionWrapper' ).eq( curQ ),
			totalQuestionsNum	= parseInt( quizElem.data( 'questions' ) ),
			questionsAnswered	= parseInt( quizElem.data( 'questions-answered' ) ),
			questionPercent		= parseInt( ( questionsAnswered / totalQuestionsNum ) * 100 ),
			quizTimer			= parseInt( quizElem.data( 'quiz-timer' ) );

		if ( ! curQElem.next().length ) {
			return;
		}

		curQElem.transition({
			animation: quizElem.data( 'transition_out' ),
			onComplete: function() {

				var next = curQElem.next();

				curQElem.hide();

				next.transition({
					animation: quizElem.data( 'transition_in' ),
					onComplete: function() {

						if ( next.hasClass( 'wq_isAd' ) ) {

							var template = next.find( 'template' );

							if ( template.length > 0 ) {
								var ad = template.html();
								template.replaceWith( ad );
							}
						}
					}
				});

				quizElem.data( 'current-question', curQ + 1 );
			}
		});

		quizElem.find( '.wq_quizProgressValue' ).animate({
			width: questionPercent + '%'
		}).text( questionPercent + '%' );

		quizElem.find( '.wq_triviaQuizTimerCtr' ).text( quizTimer );

		if ( 1 === parseInt( quizElem.data( 'auto-scroll' ) ) ) {
			$( 'html, body' ).animate({
				scrollTop: quizElem.offset().top - 35
			}, 750 );
		}
	}));

	$( document ).on( 'submit', '#wq_infoForm', function( event ) {

		event.preventDefault();

		var form			= $( this ),
			username		= form.find( '#wq_inputName' ).val(),
			email			= form.find( '#wq_inputEmail' ).val(),
			quizElem		= form.closest( '.wq_quizCtr' ),
			ajaxurl			= quizElem.data( 'ajax-url' ),
			isRetakeable	= parseInt( quizElem.data( 'retake-quiz' ) );

		if ( '' === username || '' === email ) {
			alert( 'Please enter your name and email address !' );
			return;
		}

		var formObj = {
			action: 'wq_submitInfo',
			username: username,
			email: email,
			pid: quizElem.data( 'quiz-pid' ),
			_nonce: wq_l10n.nonce
		};

		$.post( ajaxurl, formObj, function( response ) {
			if ( 2 === parseInt( response.status ) ) {

				var animationIn		= quizElem.data( 'transition_in' ),
					animationOut	= quizElem.data( 'transition_out' );

				quizElem.find( '.wq_quizForceActionCtr' ).transition({
					animation: animationOut,
					onComplete: function() {
						quizElem.find( '.wq_resultsCtr' ).transition({
							animation: animationIn
						});
					}
				});

				if ( isRetakeable ) {
					quizElem.find( '.wq_retakeQuizCtr' ).transition({ animation: animationIn });
				}
			} else {
				alert( 'Error submiting details, please try again' );
			}
		});
	});

	$( document ).on( 'click', '.wq_retakeQuizBtn', function( event ) {

		event.preventDefault();

		var quizElem		= $( this ).closest( '.wq_quizCtr' ),
			animationIn		= quizElem.data( 'transition_in' ),
			animationOut	= quizElem.data( 'transition_out' ),
			qTime			= parseInt( quizElem.data( 'quiz-timer' ) ),
			questionLayout	= quizElem.data( 'question-layout' );

		// Reset Quiz
		quizElem.data( 'current-question', 0 );
		quizElem.data( 'questions-answered', 0 );
		quizElem.data( 'correct-answered', 0 );
		quizElem.find( '.wq_quizProgressValue' ).animate({
			width: '0%'
		}).text( '' );

		// Reset All Questions
		quizElem.find( '.wq_questionsCtr > .wq_singleQuestionWrapper' ).each(function() {

			var $this = $( this );

			$this.find( '.wq_triviaQuestionExplanation' ).removeClass( 'transition visible' );
			$this.find( '.wq_triviaQuestionExplanation' ).hide();
			$this.find( '.wq_singleAnswerCtr' ).removeClass( 'wq_incorrectAnswer wq_correctAnswer chosen wq_answerSelected' );
			$this.find( '.wq_ExplanationHead' ).removeClass( 'wq_correctExplanationHead wq_wrongExplanationHead' );
			$this.data( 'question-answered', 1 );
			$this.removeClass( 'wq_questionAnswered' );
		});

		// Reset Results
		quizElem.find( '.wq_singleResultWrapper, .wq_singleResultRow' ).data( 'points', 0 );

		// Hide results and show first question
		quizElem.find( '.wq_questionsCtr' ).show();
		quizElem.find( '.wq_singleResultWrapper.transition.visible' ).transition({
			animation: animationOut,
			onComplete: function() {
				quizElem.find( '.wq_singleResultWrapper' ).hide();
				if ( 'multiple' === questionLayout ) {
					if ( qTime > 0 ) {
						quizElem.find( '.wq_questionsCtr' ).hide();
						quizElem.find( '.wq_questionsCtr > .wq_singleQuestionWrapper:eq(0)' ).show();
						quizElem.find( '.wq_triviaQuizTimerCtr' ).text( qTime );
						quizElem.find( '.wq_triviaQuizTimerInfoCtr' ).transition({
							animation: animationIn
						});
					} else {
						quizElem.find( '.wq_questionsCtr > .wq_singleQuestionWrapper:last' ).transition({
							animation: animationOut,
							onComplete: function() {
								quizElem.find( '.wq_questionsCtr > .wq_singleQuestionWrapper:eq(0)' ).transition({
									animation: animationIn
								});
							}
						});
					}
				}

				// Reset Results
				quizElem.find( '.wq_singleResultWrapper, .wq_resultsTable, .wq_shareResultCtr, .wq_resultsCtr, .wq_quizForceActionCtr, .wq_quizEmailCtr, .wq_quizForceShareCtr, .wq_retakeQuizBtn, .wq_retakeQuizCtr' ).removeClass( 'transition hidden visible' );
				quizElem.find( '.wq_resultExplanation, .wq_quizForceActionCtr, .wq_quizEmailCtr, .wq_quizForceShareCtr, .wq_retakeQuizBtn' ).hide();
			}
		});

		$( 'html, body' ).animate({
			scrollTop: quizElem.offset().top - 35
		}, 750 );

		$( this ).removeClass( 'transition visible' ).hide();
	});

	// Embed toggle
	$( document ).on( 'click', '.wq_embedToggleQuizCtr a', function( event ) {
		event.preventDefault();

		$( this ).parent().toggleClass( 'active' ).next().slideToggle( 'fast' );
	});

	// Share - Facebook
	$( document ).on( 'click', '.wq_forceShareFB', function( event ) {

		event.preventDefault();

		var quizElem	= $( this ).closest( '.wq_quizCtr' ),
			shareURL	= quizElem.data( 'share-url' ) ? quizElem.data( 'share-url' ) : document.referrer;

		var base64 = {
			id: parseInt( quizElem.data( 'quiz-pid' ) ),
			pic: 'f',
			desc: 'e'
		};

		base64 = $.param( base64 );

		FB.ui({
			method: 'share',
			href: shareURL + '?fbs=1&' + base64
		}, function( response ) {

			if ( 'undefined' === typeof response ) {
				return;
			}

			quizElem.find( '.wq_quizForceActionCtr' ).transition({
				animation: quizElem.data( 'transition_out' ),
				onComplete: function() {
					quizElem.find( '.wq_resultsCtr' ).transition({
						animation: quizElem.data( 'transition_in' )
					});
				}
			});
		});
	});

	$( document ).on( 'click', '.wq_shareFB', function( event ) {

		event.preventDefault();

		var quizElem	= $( this ).closest( '.wq_quizCtr' ),
			shareURL	= quizElem.data( 'share-url' ) ? quizElem.data( 'share-url' ) : document.referrer,
			resultElem	= quizElem.find( '.wq_singleResultWrapper:visible' ),
			description	= resultElem.find( '.wq_resultDesc' ).text(),
			picture		= resultElem.find( '.wq_resultImg' ).attr( 'src' ),
			shareText;

		if ( resultElem.hasClass( 'wq_IsTrivia' ) ) {
			var correctAnswered		= parseInt( quizElem.data( 'correct-answered' ) ),
				totalQuestionsNum	= parseInt( quizElem.data( 'questions' ) );

			shareText = wq_l10n.captionTriviaFB.replace( '%%score%%', correctAnswered ).replace( '%%total%%', totalQuestionsNum );

		} else if ( resultElem.hasClass( 'wq_IsPersonality' ) ) {
			shareText = resultElem.find( '.wq_resultTitle' ).data( 'title' );
		} else {
			shareText = quizElem.data( 'post-title' );
		}

		var base64 = {
			id: parseInt( quizElem.data( 'quiz-pid' ) ),
			rid: parseInt( resultElem.data( 'id' ) ),
			pic: picture ? 'r' : 'f',
			text: shareText,
			desc: description ? 'r' : 'e',
			ts: Date.now()
		};

		if ( quizElem.hasClass( 'fb_quiz_quiz' ) ) {
			base64.pic = picture.split( '/' ).pop().replace( '.png', '' );
			base64.nf = quizElem.data( 'user-info' ).first_name;
			base64.nl = quizElem.data( 'user-info' ).last_name;
		}

		base64 = $.param( base64 );

		FB.ui({
			method: 'share',
			href: shareURL + '?fbs=1&' + base64
		}, function() {});
	});

	// Share - Twitter
	$( document ).on( 'click', '.wq_shareTwitter', function( event ) {
		event.preventDefault();

		var quizElem	= $( this ).closest( '.wq_quizCtr' ),
			shareURL	= quizElem.data( 'share-url' ) ? quizElem.data( 'share-url' ) : document.referrer,
			resultElem	= quizElem.find( '.wq_singleResultWrapper:visible' ),
			description	= resultElem.find( '.wq_resultDesc' ).text(),
			picture		= resultElem.find( '.wq_resultImg' ).attr( 'src' ),
			shareText = '';

		if ( resultElem.hasClass( 'wq_IsTrivia' ) ) {
			var correctAnswered		= parseInt( quizElem.data( 'correct-answered' ) ),
				totalQuestionsNum	= parseInt( quizElem.data( 'questions' ) );

			shareText = wq_l10n.captionTriviaFB.replace( '%%score%%', correctAnswered ).replace( '%%total%%', totalQuestionsNum );
		} else if ( resultElem.hasClass( 'wq_IsPersonality' ) ) {
			shareText = resultElem.find( '.wq_resultTitle' ).data( 'title' );
		} else {
			shareText = quizElem.data( 'post-title' );
		}

		var base64 = {
			id: parseInt( quizElem.data( 'quiz-pid' ) ),
			rid: parseInt( resultElem.data( 'id' ) ),
			pic: picture ? 'r' : 'f',
			desc: description ? 'r' : 'e',
			text: shareText
		};

		base64 = $.param( base64 );

		window.open(
			'https://twitter.com/share?url=' + encodeURIComponent( shareURL + '?fbs=1&' + base64 ),
			'_blank',
			'width=500, height=300'
		);
	});

	// Share - Google+
	$( document ).on( 'click', '.wq_shareGP', function( event ) {

		event.preventDefault();

		var quizElem	= $( this ).closest( '.wq_quizCtr' ),
			shareURL	= quizElem.data( 'share-url' ) ? quizElem.data( 'share-url' ) : document.referrer,
			resultElem	= quizElem.find( '.wq_singleResultWrapper:visible' ),
			description	= resultElem.find( '.wq_resultDesc' ).text(),
			picture		= resultElem.find( '.wq_resultImg' ).attr( 'src' ),
			shareText = '';

		if ( resultElem.hasClass( 'wq_IsTrivia' ) ) {
			var correctAnswered		= parseInt( quizElem.data( 'correct-answered' ) ),
				totalQuestionsNum	= parseInt( quizElem.data( 'questions' ) );

			shareText = wq_l10n.captionTriviaFB.replace( '%%score%%', correctAnswered ).replace( '%%total%%', totalQuestionsNum );
		} else if ( resultElem.hasClass( 'wq_IsPersonality' ) ) {
			shareText = resultElem.find( '.wq_resultTitle' ).data( 'title' );
		} else {
			shareText = quizElem.data( 'post-title' );
		}

		var base64 = {
			id: parseInt( quizElem.data( 'quiz-pid' ) ),
			rid: parseInt( resultElem.data( 'id' ) ),
			pic: picture ? 'r' : 'f',
			desc: description ? 'r' : 'e',
			text: shareText
		};

		base64 = $.param( base64 );

		window.open(
			'https://plus.google.com/share?url=' + encodeURIComponent( shareURL + '?fbs=1&' + base64 ),
			'_blank',
			'width=500, height=300'
		);
	});

	// Share - VK
	$( document ).on( 'click', '.wq_shareVK', function( event ) {

		event.preventDefault();

		var quizElem = $( this ).closest( '.wq_quizCtr' ),
			shareURL = quizElem.data( 'share-url' ) ? quizElem.data( 'share-url' ) : document.referrer;

		window.open(
			'http://vk.com/share.php?url=' + shareURL,
			'_blank',
			'width=500, height=300'
		);
	});

	// Document Ready
	$( document ).ready(function() {

		function flipDescResizeAndReady( event ) {

			$( '.wq_IsFlip .back' ).each(function() {

				var $this = $( this ),
					bImg = $this.find( 'img' ).attr( 'src' ),
					titleH = $( this ).siblings( '.item_top' ).height();

				$this.css( 'top', titleH + 'px' );

				if ( '' === bImg ) {

					$this.siblings( '.front' ).find( 'img' ).on( 'load', function() {
						$this.find( '.desc' ).height( $( this ).height() );
					});

					if ( 'resize' === event.type ) {
						var imgHeight = $this.siblings( '.front' ).find( 'img' ).height();
						$this.find( '.desc' ).height( imgHeight );
					}
				}
			});
		}

		$( document ).on( 'ready', flipDescResizeAndReady );
		$( window ).on( 'resize', flipDescResizeAndReady );
		$( window ).trigger( 'resize' );
	});

	// Trivia
	function processResults( quizElem ) {

		var forceAction			= parseInt( quizElem.data( 'force-action' ) ),
			animationIn			= quizElem.data( 'transition_in' ),
			correctAnswered		= parseInt( quizElem.data( 'correct-answered' ) ),
			totalQuestionsNum	= parseInt( quizElem.data( 'questions' ) ),
			endAnswers			= quizElem.data( 'end-answers' ),
			isRetakeable		= parseInt( quizElem.data( 'retake-quiz' ) );

		quizElem.find( '.wq_triviaQuizTimerCtr, .wq_continue' ).hide();

		if ( endAnswers ) {

			quizElem.find( '.wq_singleAnswerCtr.wq_IsTrivia' ).each(function() {

				var $answer = $( this );

				if ( $answer.hasClass( 'wq_correctEndAnswer' ) ) {
					$answer.removeClass( 'wq_correctEndAnswer' ).addClass( 'wq_correctAnswer' );
				} else if ( $answer.hasClass( 'wq_incorrectEndAnswer' ) ) {
					$answer.removeClass( 'wq_incorrectEndAnswer' ).addClass( 'wq_incorrectAnswer' );
				}
			});

			quizElem.find( '.wq_triviaQuestionExplanation' ).show( 'slow' );
			$( 'html, body' ).animate({
				scrollTop: quizElem.find( '.wq_singleQuestionWrapper:first-of-type' ).offset().top - 95
			}, 750 );
		}

		if ( forceAction > 0 ) {

			var selector = (function() {

				if ( 1 === forceAction ) {
					return '.wq_quizEmailCtr';
				}

				if ( 2 === forceAction ) {
					return '.wq_quizForceShareCtr';
				}

			})();

			quizElem.find( '.wq_quizForceActionCtr' ).transition({
				animation: animationIn,
				onComplete: function() {
					quizElem.find( selector ).transition({
						animation: animationIn
					});
				}
			});

			quizElem.find( '.wq_resultsCtr, .wq_retakeQuizCtr' ).hide();
		}

		var resultFound = false;

		quizElem.find( '.wq_singleResultWrapper' ).each(function() {
			var $result = $( this ),
				min		= parseInt( $result.data( 'min' ) ),
				max		= parseInt( $result.data( 'max' ) );

			if ( correctAnswered >= min && correctAnswered <= max && ! resultFound ) {
				resultFound = true;
				var title = wq_l10n.captionTrivia.replace( '%%score%%', correctAnswered ).replace( '%%total%%', totalQuestionsNum );
				$result.find( '.wq_resultScoreCtr' ).text( title );
				$result.transition({ animation: animationIn });
				return;
			}
		});

		if ( isRetakeable ) {
			quizElem.find( '.wq_retakeQuizBtn' ).transition({ animation: animationIn });
		}

		$.post( quizElem.data( 'ajax-url' ), {
			action: 'wq_quizResults',
			correct: correctAnswered,
			pid: parseInt( quizElem.data( 'quiz-pid' ) ),
			type: 'trivia',
			_nonce: wq_l10n.nonce
		});
	}

	$( document ).on( 'click', '.wq_singleQuestionWrapper:not(.wq_questionAnswered) .wq_singleAnswerCtr.wq_IsTrivia', function( event ) {
		event.preventDefault();

		var $this				= $( this ),
			isCorrect			= parseInt( $this.data( 'crt' ) ),
			questionElem		= $this.closest( '.wq_singleQuestionWrapper' ),
			quizElem			= $this.closest( '.wq_quizCtr' ),
			totalQuestionsNum	= parseInt( quizElem.data( 'questions' ) ),
			questionsAnswered	= parseInt( quizElem.data( 'questions-answered' ) ) + 1,
			correctAnswered		= parseInt( quizElem.data( 'correct-answered' ) ),
			curQ				= parseInt( quizElem.data( 'current-question' ) ),
			questionLayout		= quizElem.data( 'question-layout' ),
			endAnswers			= quizElem.data( 'end-answers' );

		questionElem.addClass( 'wq_questionAnswered' );

		// Process Correct Answer
		var correctClass	= endAnswers ? 'wq_correctEndAnswer' : 'wq_correctAnswer',
			incorrectClass	= endAnswers ? 'wq_incorrectEndAnswer' : 'wq_incorrectAnswer';

		if ( 1 === isCorrect  ) {

			correctAnswered++;
			$this.addClass( correctClass + ' chosen' );
			questionElem.find( '.wq_triviaQuestionExplanation .wq_ExplanationHead' ).text( wq_l10n.correct ).addClass( 'wq_correctExplanationHead' );
			quizElem.data( 'correct-answered', correctAnswered );

		} else {
			questionElem.find( '.wq_singleAnswerCtr' ).each(function() {
				if ( 1 === $( this ).data( 'crt' ) ) {
					$( this ).addClass( correctClass );
				}
			});
			$this.addClass( incorrectClass + ' chosen' );
			questionElem.find( '.wq_triviaQuestionExplanation .wq_ExplanationHead' ).text( wq_l10n.wrong ).addClass( 'wq_wrongExplanationHead' );
		}

		if ( 'single' === questionLayout ) {
			curQ = parseInt( quizElem.data( 'current-question' ) );
			quizElem.data( 'current-question', curQ + 1 );
		} else {
			questionElem.find( '.wq_continue' ).show();
		}

		quizElem.data( 'questions-answered', questionsAnswered );

		if ( ! endAnswers ) {
			questionElem.find( '.wq_triviaQuestionExplanation' ).show();
		}

		if ( 1 === parseInt( quizElem.data( 'auto-scroll' ) ) ) {
			var nextScroll = questionElem.next().length ? questionElem.next().offset() : quizElem.find( '.wq_resultsCtr' ).offset();
			$( 'html, body' ).animate({
				scrollTop: nextScroll.top - 95
			}, 750 );
		}

		if ( totalQuestionsNum === questionsAnswered ) {
			quizElem.find( '.wq_quizProgressValue' ).animate({ width: '100%' }).text( '100%' );
			clearInterval( window.timerInterval );
			processResults( quizElem );
			return;
		}
	});

	$( document ).on( 'click', '.wq_beginQuizCtr', function( event ) {

		event.preventDefault();

		var quizElem		= $( this ).closest( '.wq_quizCtr' ),
			animationIn		= quizElem.data( 'transition_in' ),
			animationOut	= quizElem.data( 'transition_out' ),
			questionCtr		= quizElem.find( '.wq_questionsCtr' );

		quizElem.find( '.wq_triviaQuizTimerCtr' ).show();
		quizElem.find( '.wq_triviaQuizTimerInfoCtr' ).transition({
			animation: animationOut,
			onComplete: function() {
				questionCtr.transition({
					animation: animationIn,
					onComplete: function() {

						questionCtr.removeClass( 'visible' );
						questionCtr.attr( 'style', 'display:block;' );

						window.timerInterval = setInterval(function() {

							var curSec		= parseInt( quizElem.find( '.wq_triviaQuizTimerCtr' ).text() ) - 1,
								quizTimer	= parseInt( quizElem.data( 'quiz-timer' ) );

							if ( 0 === curSec ) {
								var curQ		= parseInt( quizElem.data( 'current-question' ) ),
									curQElem	= $( '.wq_questionsCtr > .wq_singleQuestionWrapper' ).eq( curQ );

								if ( ! curQElem.next().length ) {

									clearInterval( window.timerInterval );
									processResults( quizElem );
									return;
								}

								curQElem.find( '.wq_btn-continue' ).trigger( 'click' );
								quizElem.find( '.wq_triviaQuizTimerCtr' ).text( quizTimer );
								return;
							}

							quizElem.find( '.wq_triviaQuizTimerCtr' ).text( curSec );

						}, 1000 );
					}
				});
			}
		});
	});

	// Personality
	$( document ).on( 'click', '.wq_singleQuestionWrapper:not(.wq_questionAnswered) .wq_singleAnswerCtr.wq_IsPersonality', function( event ) {

		event.preventDefault();

		var $this				= $( this ),
			quizElem			= $this.closest( '.wq_quizCtr' ),
			resultsInfo			= JSON.parse( $this.find( '.wq_singleAnswerResultCtr' ).val() ),
			curQElem			= $this.closest( '.wq_singleQuestionWrapper' ),
			questionsAnswered	= parseInt( quizElem.data( 'questions-answered' ) ),
			totalQuestionsNum	= parseInt( quizElem.data( 'questions' ) ),
			animationIn			= quizElem.data( 'transition_in' ),
			forceAction			= parseInt( quizElem.data( 'force-action' ) ),
			isRetakeable		= parseInt( quizElem.data( 'retake-quiz' ) ),
			questionLayout		= quizElem.data( 'question-layout' ),
			autoScroll			= parseInt( quizElem.data( 'auto-scroll' ) );

		curQElem.addClass( 'wq_questionAnswered' );

		// Remove Any Points from Previous Selected Result if Any
		curQElem.find( '.wq_singleAnswerCtr.wq_answerSelected' ).each(function() {

			var oldResInfo = JSON.parse( $( this ).find( '.wq_singleAnswerResultCtr' ).val() );

			if ( '' !== oldResInfo ) {

				oldResInfo.forEach(function( ele, ind, arr ) {

					var resultElem		= quizElem.find( '.wq_singleResultWrapper[data-rid="' + ind + '"]' ),
						resultPoints	= parseInt( resultElem.data( 'points' ) ) - parseInt( ele.points );

					resultElem.data( 'points', resultPoints );
				});
			}
		});

		// Add new Points
		if ( '' !== resultsInfo )	{

			resultsInfo.forEach(function( ele, ind, arr ) {

				var resultElem    = quizElem.find( '.wq_singleResultWrapper[data-rid="' + ind + '"]' ),
					resultPoints  = parseInt( resultElem.data( 'points' ) ) + parseInt( ele.points );

				resultElem.data( 'points', resultPoints );
			});
		}

		// Increment Questions Answered
		if ( 1 === parseInt( curQElem.data( 'question-answered' ) ) ) {

			questionsAnswered++;
			curQElem.data( 'question-answered', 2 );
			quizElem.data( 'questions-answered', questionsAnswered );
		}

		$this.addClass( 'wq_answerSelected' );

		if ( 'single' === questionLayout ) {

			var curQ = parseInt( quizElem.data( 'current-question' ) );

			quizElem.data( 'current-question', curQ + 1 );

			if ( curQElem.next().length && 1 === autoScroll ) {
				$( 'html, body' ).animate({
					scrollTop: curQElem.next().offset().top - 75
				}, 750 );
			}
		} else {
			curQElem.find( '.wq_btn-continue' ).trigger( 'click' );
		}

		if ( totalQuestionsNum !== questionsAnswered ) {
			return;
		}

		quizElem.find( '.wq_quizProgressValue' ).animate({ width: '100%' }).text( '100%' );
		$( 'html, body' ).animate({
			scrollTop: quizElem.find( '.wq_resultsCtr' ).offset().top - 75
		}, 750 );

		var resultElem	= null,
			maxPoints	= 0;

		quizElem.find( '.wq_singleResultWrapper' ).each(function() {

			var resultPoints = parseInt( $( this ).data( 'points' ) );

			if ( resultPoints > maxPoints ) {
				maxPoints	= resultPoints;
				resultElem	= $( this );
			}
		});

		$.post( quizElem.data( 'ajax-url' ), {
			action: 'wq_quizResults',
			rid: resultElem.data( 'rid' ),
			pid: parseInt( quizElem.data( 'quiz-pid' ) ),
			type: 'personality',
			_nonce: wq_l10n.nonce
		});

		if ( forceAction > 0 ) {

			var selector = (function() {

				if ( 1 === forceAction ) {
					return '.wq_quizEmailCtr';
				}

				if ( 2 === forceAction ) {
					return '.wq_quizForceShareCtr';
				}

			})();

			quizElem.find( '.wq_quizForceActionCtr' ).transition({
				animation: animationIn,
				onComplete: function() {
					quizElem.find( selector ).transition({
						animation: animationIn
					});
				}
			});

			resultElem.show();
			quizElem.find( '.wq_resultsCtr, .wq_retakeQuizCtr' ).hide();
		}

		resultElem.transition({ animation: animationIn });

		if ( isRetakeable ) {
			quizElem.find( '.wq_retakeQuizBtn' ).transition({ animation: animationIn });
		}
	});

	// FB Quiz

	var processQuiz = function( quizElem ) {

		quizElem.find( '.wq_singleQuestionWrapper .wq_loader-container' ).show();

		FB.api( '/me?fields=name,gender,first_name,last_name,email', function( res ) {

			var userInfo = res;
			quizElem.data( 'user-info', userInfo );

			FB.api( '/me/friends',  function( res ) {

				userInfo.friends = res.data;

				var data = {
					action: 'wq_submitFbInfo',
					pid: quizElem.data( 'quiz-pid' ),
					user: userInfo,
					profile: quizElem.data( 'quiz-profile' ),
					_nonce: wq_l10n.nonce
				};

				$.post( quizElem.data( 'ajax-url' ), data, function( res ) {

					if ( 2 === res.status ) {

						var img = quizElem.find( '.wq_resultImg' );
						img.attr( 'src', res.src );
						img.load(function() {
							quizElem.find( '.wq_resultDesc' ).html( res.desc );
							quizElem.find( '.wq_singleQuestionWrapper.wq_IsFb, .wq_singleQuestionWrapper .wq_loader-container' ).hide( 'slow' );
							quizElem.find( '.wq_singleResultWrapper.wq_IsFb' ).data( 'id', res.key ).show( 'slow' );
						});
					} else {

						console.log( res.error );
						setTimeout(function() {
							window.location.href = quizElem.data( 'share-url' );
						}, 200 );
					}
				});
			});
		});
	};

	window.getLogin = function( response ) {
		if ( 'connected' === response.status ) {
			var button = $( '.wq_questionLogin button' );
			button.removeClass( 'wq_loginFB' ).addClass( 'wq_playFB' );
		}
	};

	$( document ).on( 'click', '.wq_singleQuestionWrapper .wq_loginFB', function( event ) {

		event.preventDefault();

		var quizElem = $( this ).closest( '.wq_quizCtr' );

		FB.login(function() {
			FB.getLoginStatus(function( response ) {
				if ( 'connected' === response.status ) {
					processQuiz( quizElem );
				} else {
					setTimeout(function() {
						window.location.href = quizElem.data( 'share-url' );
					}, 200 );
				}
			}, true );
		}, { scope: 'public_profile,email,user_friends' });
	});

	$( document ).on( 'click', '.wq_singleQuestionWrapper .wq_playFB', function( event ) {

		event.preventDefault();

		processQuiz( $( this ).closest( '.wq_quizCtr' ) );
	});

})( jQuery );
