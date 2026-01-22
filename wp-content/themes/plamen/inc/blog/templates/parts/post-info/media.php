<div class="qodef-e-media">
	<?php switch ( get_post_format() ) {
		case 'gallery':
			plamen_template_part( 'blog', 'templates/parts/post-format/gallery' );
			break;
		case 'video':
			plamen_template_part( 'blog', 'templates/parts/post-format/video' );
			break;
		case 'audio':
			plamen_template_part( 'blog', 'templates/parts/post-format/audio' );
			break;
		default:
			plamen_template_part( 'blog', 'templates/parts/post-info/image' );
			break;
	} ?>
</div>