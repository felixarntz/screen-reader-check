<?php
/**
 * VideoAlternatives test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the VideoAlternatives test.
 *
 * @since 1.0.0
 */
class VideoAlternatives extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'video_alternatives';
		$this->title            = __( 'Alternatives for video content', 'screen-reader-check' );
		$this->description      = __( 'Silent video files that convey information must have proper media alternatives. For visual video content an audio description is required.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.2.1 Audio-only and Video-only (Prerecorded)', 'screen-reader-check' );
		$this->guideline_anchor = 'media-equiv-av-only-alt';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20/#media-equiv-audio-desc',
			'title'  => __( '1.2.3 Audio Description or Media Alternative (Prerecorded)', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H53',
			'title'  => __( 'Using the body of the <code>object</code> element', 'screen-reader-check' ),
		);
	}

	/**
	 * Runs the test on a given DOM object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array                        $result The default result array with keys
	 *                                             `type`, `messages` and `request_data`.
	 * @param ScreenReaderCheck\Parser\Dom $dom    The DOM object to check.
	 * @return array The modified result array.
	 */
	protected function run( $result, $dom ) {
		$videos = $dom->find( 'object,embed,video' );

		if ( count( $videos ) === 0 ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no video files in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$video_requests = array();

		$found = false;
		foreach ( $videos as $video ) {
			$src = null;
			switch ( $video->getTagName() ) {
				case 'object':
					$src = $video->getAttribute( 'data' );
					if ( ! $src || ! $this->is_video_file( $src ) ) {
						$src = null;
					}
					break;
				case 'embed':
					$src = $video->getAttribute( 'src' );
					if ( ! $src || ! $this->is_video_file( $src ) ) {
						$src = null;
					}
					break;
				case 'video':
				default:
					$source = $video->find( 'source', false, true );
					if ( $source ) {
						$src = $source->getAttribute( 'src' );
					}
			}

			if ( $src && is_string( $src ) ) {
				$found = true;

				$sanitized_src = $this->sanitize_src( $src );

				$alternative = $video->find( 'object,embed,audio', false, true );
				$alternative_src = null;
				if ( $alternative ) {
					switch ( $alternative->getTagName() ) {
						case 'object':
							$alternative_src = $alternative->getAttribute( 'data' );
							break;
						case 'embed':
						case 'video':
						default:
							$alternative_src = $alternative->getAttribute( 'src' );
					}
				}

				$video_type = $this->get_option( 'video_type_' . $sanitized_src );
				if ( $video_type ) {
					if ( 'video_only' === $video_type ) {
						if ( ! $alternative_src || ! $this->is_audio_file( $alternative_src ) ) {
							$has_alternative_audio_or_text = $this->get_option( 'video_alternative_audio_or_text_' . $sanitized_src );
							if ( $has_alternative_audio_or_text ) {
								if ( 'yes' === $has_alternative_audio_or_text ) {
									$result['message_codes'][] = 'warning_alternative_content_outside_of_body';
									$result['messages'][] = $this->wrap_message( __( 'The alternative content for the following silent video should be located in the element body:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $video->outerHtml() ), $video->getLineNo() );
									$has_warnings = true;
								} else {
									$result['message_codes'][] = 'error_missing_alternative_content';
									$result['messages'][] = $this->wrap_message( __( 'The following silent video is missing an audio or text alternative:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $video->outerHtml() ), $video->getLineNo() );
									$has_errors = true;
								}
							} else {
								$result['request_data'][] = array(
									'slug'          => 'video_alternative_audio_or_text_' . $sanitized_src,
									'type'          => 'select',
									'label'         => __( 'Alternative Audio or Text available?', 'screen-reader-check' ),
									'description'   => sprintf( __( 'Specify whether an audio or text alternative is provided for the silent video %s.', 'screen-reader-check' ), $this->linkify_src( $src ) ),
									'options'       => array(
										array(
											'value'   => 'yes',
											'label'   => __( 'Yes', 'screen-reader-check' ),
										),
										array(
											'value'   => 'no',
											'label'   => __( 'No', 'screen-reader-check' ),
										),
									),
									'default'       => 'no',
								);
							}
						}
					} else {
						if ( ! $alternative_src || ! $this->is_audio_file( $alternative_src ) ) {
							$has_audio_description = $this->get_option( 'video_alternative_audio_description_' . $sanitized_src );
							if ( $has_audio_description ) {
								if ( 'yes' !== $has_audio_description ) {
									$result['message_codes'][] = 'error_missing_audio_description';
									$result['messages'][] = $this->wrap_message( __( 'The following video is missing an audio description:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $video->outerHtml() ), $video->getLineNo() );
									$has_errors = true;
								}
							} else {
								$result['request_data'][] = array(
									'slug'          => 'video_alternative_audio_description_' . $sanitized_src,
									'type'          => 'select',
									'label'         => __( 'Alternative Audio Description available?', 'screen-reader-check' ),
									'description'   => sprintf( __( 'Specify whether an audio description is provided for the video %s.', 'screen-reader-check' ), $this->linkify_src( $src ) ),
									'options'       => array(
										array(
											'value'   => 'yes',
											'label'   => __( 'Yes', 'screen-reader-check' ),
										),
										array(
											'value'   => 'no',
											'label'   => __( 'No', 'screen-reader-check' ),
										),
									),
									'default'       => 'no',
								);
							}
						}
					}
				} else {
					if ( ! in_array( $sanitized_src, $video_requests ) ) {
						$video_requests[] = $sanitized_src;
						$result['request_data'][] = array(
							'slug'          => 'video_type_' . $sanitized_src,
							'type'          => 'select',
							'label'         => __( 'Video Type', 'screen-reader-check' ),
							'description'   => sprintf( __( 'Specify whether the video %s is video-only content or whether it also contains audio.', 'screen-reader-check' ), $this->linkify_src( $src ) ),
							'options'       => array(
								array(
									'value'   => 'video_only',
									'label'   => __( 'Video-only', 'screen-reader-check' ),
								),
								array(
									'value'   => 'has_audio',
									'label'   => __( 'Video with audio', 'screen-reader-check' ),
								),
							),
							'default'       => 'has_audio',
						);
					}
				}
			}
		}

		if ( ! $found ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no video files in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All videos in the HTML code have valid alternative content provided.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Checks whether the source is a video file.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $src The source.
	 * @return bool True if it is a video file, false otherwise.
	 */
	protected function is_video_file( $src ) {
		return $this->src_has_extension( $src, array( '3g2',  '3gp', '3gpp', 'asf', 'avi',  'divx', 'dv',   'flv',  'm4v',   'mkv',  'mov',  'mp4',  'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt',  'rm', 'vob', 'wmv' ) );
	}

	/**
	 * Checks whether the source is a audio file.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $src The source.
	 * @return bool True if it is a audio file, false otherwise.
	 */
	protected function is_audio_file( $src ) {
		return $this->src_has_extension( $src, array( 'aac', 'ac3',  'aif',  'aiff', 'm3a',  'm4a',   'm4b',  'mka',  'mp1',  'mp2',  'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma' ) );
	}
}
