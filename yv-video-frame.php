<?php
/*
	Plugin Name: Fast and Responsive Youtube Vimeo Embed
	Plugin URI: http://webpuzzlemaster.com
	Description: Youtube, Vimeo Video Frame for WPM Only, Uses shortcode YouTube: [ytvf id="VideoIdHere"] and Vimeo: [ytvf id="VideoIdHere" type="vimeo"]
	Author: WebPuzzleMaster
	Version: 1.0
	Author URI: https://webpuzzlemaster.com/
*/


define( 'YTVF_VERSION', '1.0' );
define( 'YTVF_MINIMUM_WP_VERSION', '4.0' );
define( 'YTVF_DIR', plugin_dir_path( __FILE__ ) );
define( 'YTVF_URL', plugins_url( '',__FILE__ ) );

class YTVF {
	private static $initiated = false;
	private static $wpmscv_db_version = '1.0';
	private static $usedOnce = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;
		add_shortcode( 'ytvf', array( 'YTVF', 'ytvf_shortcode' ) );
	}

	public static function get_video_thumb($_id, $type){
		if( $type == 'vimeo' ){
			$request = wp_remote_get( "https://vimeo.com/api/v2/video/{$_id}.json" );
			if( is_wp_error( $request ) ) {
				return '';
			}
			$body = json_decode(wp_remote_retrieve_body( $request ));
			if( is_array($body) && count($body) > 0 ){
				$body = array_pop($body);
				return '<img class="thumbs" src="'.$body->thumbnail_large.'">';
			} else {
				return '';
			}
		} else {
			return '<img class="thumbs" src="https://i3.ytimg.com/vi/'.$_id.'/maxresdefault.jpg">';
		}
	}

	public static function ytvf_shortcode($attr){
		$attr = shortcode_atts(array(
            "type" => 'youtube',
            "id" => ""
        ), $attr);
        $videoId 	= sanitize_text_field($attr['id']);
        $videoType 	= sanitize_text_field($attr['type']);

        if( strlen($videoId) > 8 ){
			ob_start();
			$type = ( $videoType == 'vimeo' ) ? 'vimeo' : 'youtube';
			echo '<div class="ytvf ytvf_dark_frame ytvf_wrapper">';
				echo '<div class="ytvf_responsive_video_container">'; 
					echo SELF::get_video_thumb($videoId, $type);
					echo '<img class="playbutton" data-id="'.$videoId.'" data-type="'.$type.'" src="https://s.ytimg.com/yts/img/large_play_button_ringo-vfljWXIdx.png">';
				echo '</div>';
			echo '</div>';

			?>

			<?php if(!SELF::$usedOnce): ?>
			<script type="text/javascript">
			jQuery(document).ready(function($){
				jQuery('.ytvf_responsive_video_container .playbutton').on('click', function(e){
					var _this 	= $(this);
					var _id 	= _this.attr('data-id');
					if( _this.attr('data-type') == 'youtube' )
						var _frame  = '<iframe src="https://www.youtube.com/embed/'+_id+'?rel=0&amp;controls=0&amp;showinfo=0&color=red&autoplay=1" frameborder="0" allowfullscreen="allowfullscreen"></iframe>';
					else
						var _frame  = '<iframe src="https://player.vimeo.com/video/'+_id+'?autoplay=1&title=0&byline=0&portrait=0" width="840" height="473" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
					_this.closest('.ytvf_responsive_video_container').html(_frame);
				})
			});
			</script>
			
			<style type="text/css">
				.ytvf.ytvf_dark_frame {
				    background-color: #f6f6f6;
				    background-image: -webkit-linear-gradient(#000 0%,#656565 50%,#000 50%,#000 100%);
				    background-image: linear-gradient(#000 0%,#656565 50%,#000 50%,#000 100%); 
				    box-shadow: 0 0 3px 0 rgba(0,0,0,0.95);
				    border-radius: 15px;
				    padding: 20px;
				    margin-bottom: 32px;
				}
				.ytvf_wrapper{
					max-height: 100%;
				    max-width: 100%;
				    box-shadow: 0 0 1px 0 rgba(0,0,0,0.42);
				    background: #f6f6f6;
				    border: 15px solid #fff;
				    padding: 0;
				    position: relative;
				    z-index: 1;
				    -webkit-box-sizing: content-box;
				    -moz-box-sizing: content-box;
				    -ms-box-sizing: content-box;
				    box-sizing: content-box;
				}
				.ytvf_wrapper:after{
					background: url('data:image/png;base64,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') no-repeat;
				    border: 0;
				    content: "";
				    height: 34px;
				    position: absolute;
				    left: 0;
				    right: 0;
				    bottom: -50px;
				    width: 100%;
				    z-index: -1;
				    background-position: center top;
				    background-size: contain;
				}
				.ytvf_wrapper div{
					-webkit-box-sizing: content-box;
				    -moz-box-sizing: content-box;
				    -ms-box-sizing: content-box;
				    box-sizing: content-box;
				}
				.ytvf_responsive_video_container {
				    box-shadow: 0 0 3px 0 rgba(0,0,0,0.56);
				    background: #f6f6f6;
				}
				.ytvf_responsive_video_container {
				    position: relative;
				    height: 0;
				    overflow: hidden;
				    padding-bottom: 56.25%;
				    display: block;
				    border-radius: 0px;
				}
				.ytvf_responsive_video_container iframe, .ytvf_responsive_video_container object, .ytvf_responsive_video_container embed {
				    position: absolute;
				    top: 0;
				    left: 0;
				    width: 100% !important;
				    height: 100% !important;
				    max-width: 100%;
				}
				.playbutton{
					position: absolute;
				    left: 50%;
				    top: 50%;
				    width: 70px;
				    margin-left: -35px;
				    margin-top: -35px;
				    cursor: pointer;
				}
				.ytvf img {
				    max-width: 100%;
				    transform: scale(1);
				}
				.ytvf img.thumbs{
					width: 100%;
				}
				@media screen and (max-width: 768px) {
					.ytvf.ytvf_dark_frame{
						padding: 10px;
					    border-radius: 10px;
					    border-width: 10px;
					}
					.ytvf.ytvf_dark_frame:after{
						bottom: -45px;
					}
				}
			</style>
			<?php endif; ?>

			<?php
			SELF::$usedOnce = true; // skip js, style if already exists
			return ob_get_clean();

        }

	}
}

add_action( 'init', array( 'YTVF', 'init' ) );