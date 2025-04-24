<?php
/**
 * Views: Example Controls View
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Views;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\View;

#[\AllowDynamicProperties]

/**
 * Sets up the Example Controls view.
 *
 * @since 1.0.0
 */
class Example_Controls_View implements View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {

		return array(
			'example-controls' => array(
				'label'    => __( 'Overview', 'affiliatewp-affiliate-portal' ),
				'desc'     => __( 'Displays examples for all reusable Affiliate Portal controls.', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 1,
			),
			/**
			* Base extending control sections
			*/
			'base-heading' => array(
				'wrapper'  => true,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
				'priority' => 2,
			),
			'card-control-section' => array(
				'label'    => __( 'Card Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 3,
			),
			'card-group-control-section' => array(
				'label'    => __( 'Card Group Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 3,
			),
			'chart-control-section' => array(
				'label'    => __( 'Chart Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 3,
			),
			'code-block-control-section' => array(
				'label'    => __( 'Code Block Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 3,
			),
			'div-with-copy-control-section' => array(
				'label'    => __( 'Div With Copy Control', 'affiliatewp-affiliate-portal' ),
				'desc'     => __( 'Note: the copy button will only display if SSL is configured.', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 4,
			),
			'heading-control-section' => array(
				'label'    => __( 'Heading Control', 'affiliatewp-affiliate-portal' ),
				'desc'     => __( 'h1-h6', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 4,
			),
			'image-control-section' => array(
				'label'    => __( 'Image Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 4,
			),
			'icon-control-section' => array(
				'label'    => __( 'Icon Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 4,
			),
			'label-control-section' => array(
				'label'    => __( 'Label Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 5,
			),
			'link-control-section' => array(
				'label'    => __( 'Link Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 5,
			),
			'list-control-section' => array(
				'label'    => __( 'List Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 5,
			),
			'paragraph-control-section' => array(
				'label'    => __( 'Paragraph Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 5,
			),
			'table-control-section' => array(
				'label'    => __( 'Table Controls', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 6,
			),
			'wrapper-control-section' => array(
				'label'    => __( 'Wrapper Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 6,
			),
			/**
			* Form extending control sections
			*/
			'form-heading' => array(
				'wrapper'  => true,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
				'priority' => 8,
			),
			'select-control-section' => array(
				'label'    => __( 'Select Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 9,
			),
			'textarea-control-section' => array(
				'label'    => __( 'Textarea Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 9,
			),
			/**
			* Button control sections
			*/
			'button-heading' => array(
				'wrapper'  => true,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
				'priority' => 10,
			),
			'button-control-section' => array(
				'label'    => __( 'Button Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 11,
			),
			'copy-button-control-section' => array(
				'label'    => __( 'Copy Button Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 11,
			),
			'submit-button-control-section' => array(
				'label'    => __( 'Submit Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 11,
			),
			/**
			* Input control sections
			*/
			'input-heading' => array(
				'wrapper'  => true,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
				'priority' => 12,
			),
			'date-control-section' => array(
				'label'    => __( 'Date Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 13,
			),
			'email-control-section' => array(
				'label'    => __( 'Email Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 13,
			),
			'hidden-control-section' => array(
				'label'    => __( 'Hidden Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 13,
			),
			'number-control-section' => array(
				'label'    => __( 'Number Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 14,
			),
			'password-control-section' => array(
				'label'    => __( 'Password Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 14,
			),
			'text-input-control-section' => array(
				'label'   => __( 'Text Input Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority'  => 14,
			),
			/**
			* Checkable control sections
			*/
			'checkable-heading' => array(
				'wrapper'  => true,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
				'priority' => 15,
			),
			'checkbox-control-section' => array(
				'label'    => __( 'Checkbox Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 16,
			),
			'radio-control-section' => array(
				'label'    => __( 'Radio Control', 'affiliatewp-affiliate-portal' ),
				'wrapper'  => true,
				'priority' => 16,
			),
			/**
			* Atts/Args of base, form, button, input, and checkable controls
			*/
			'atts-args-info' => array(
				'wrapper' => true,
				'columns' => array(
					'header'  => 3,
					'content' => 3,
				),
			),

		);
	}

	/**
	 * Retrieves the view controls.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Controls.
	 */
	public function get_controls() {

		$title_attributes = array(
			'class' => 'text-center',
		);

		$example_card_data = array(
		    array(
		        'title'     => __( 'Example Card Group #1', 'affiliatewp-functionality' ),
				'url'       => '#',
				'description' => 'Description 1',
				'icon'	    => 'currency-dollar',
		    ),
		    array(
		        'title'     => __( 'Example Card Group #2', 'affiliatewp-functionality' ),
				'url'       => '#',
				'description' => 'Description 2',
				'icon'	    => 'cursor-click',
		    ),
		    array(
		        'title'     => __( 'Example Card Group #3', 'affiliatewp-functionality' ),
				'url'       => '#',
				'description' => 'Description 3',
				'icon'	    => 'scale',
		    )
		);

		$stat_cards = array_map( function( $link ) {
			return array(
				'title'       => $link['title'],
				'data'        => 100,
				'compare'     => 20,
				'link'        => $link['url'],
				'icon'        => $link['icon'],
				'layout'      => 'stat',
				'link_label'  => 'View resource',
				'link_target' => '_blank',
			);
		 }, $example_card_data );

		$info_cards = array_map( function( $link ) {
			return array(
				'title'       => $link['title'],
				'data'        => $link['description'],
				'link'        => $link['url'],
				'icon'        => 'document-text',
				'layout'      => 'info',
				'link_label'  => 'View resource',
				'link_target' => '_blank',
			);
		 }, $example_card_data );

		/*
		 * <pre> tags treat the whitespace even here in the source as real
		 * whitespace, thus zero indent.
		 */
		$example_code_block =
"// Example of this code block example (very meta):
new Controls\Code_Block_Control( array(
	'id'      => 'example-code-block',
	'view_id' => 'example-controls',
	'section' => 'code-block-control-section',
	'args' => array(
		'data'      => \$example_code_block,
		'show_copy' => false,
	),
) )";

		$table_test_data = array(
			array(
				'affiliate' => 'Annabelle',
				'amount'    => '3.23',
			),
			array(
				'affiliate' => 'Bob',
				'amount'    => '3',
			),
			array(
				'affiliate' => 'Clay',
				'amount'    => '2.5',
			),
		);

		return array(
			/**
			* Overview Controls
			*/
			new Controls\Heading_Control( array(
				'id'       => 'overview-base-heading',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( '0. Base control superclass', 'affiliatewp-affiliate-portal' ),
					'level' => 3,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'       => 'overview-base-desc',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( 'These controls extend the Base control class:', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\List_Control( array(
				'id'       => 'overview-base-list',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args' 	   => array(
					'list_type' => 'ordered',
					'items'   => $this->get_link_list( 'base' ),
				),
				'atts' => array(
					'class' => 'text-indigo-600',
				),
			) ),
			new Controls\Heading_Control( array(
				'id'       => 'overview-form-heading',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( '1. Base > Form Control', 'affiliatewp-affiliate-portal' ),
					'level'  => 3,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'       => 'overview-form-desc',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( 'The Form control extends Base control for activating form-specific features. These controls extend the Form control class:', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\List_Control( array(
				'id'       => 'overview-form-list',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args' 	   => array(
					'list_type' => 'ordered',
					'items'   => $this->get_link_list( 'form' ),
				),
				'atts' => array(
					'class' => 'text-indigo-600',
				),
			) ),
			new Controls\Heading_Control( array(
				'id'       => 'overview-button-heading',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( '2. Base > Form > Button Control', 'affiliatewp-affiliate-portal' ),
					'level'  => 3,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'       => 'overview-button-desc',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( 'The Button control extends Form control. These use the Button control class:', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\List_Control( array(
				'id'       => 'overview-button-list',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args' 	   => array(
					'list_type' => 'ordered',
					'items'   => $this->get_link_list( 'button' ),
				),
				'atts' => array(
					'class' => 'text-indigo-600',
				),
			) ),
			new Controls\Heading_Control( array(
				'id'       => 'overview-input-heading',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( '3. Base > Form > Input Control', 'affiliatewp-affiliate-portal' ),
					'level'  => 3,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'       => 'overview-input-desc',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( 'The Input control extends Form control. These extend the Input control class:', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\List_Control( array(
				'id'       => 'overview-input-list',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args' 	   => array(
					'list_type' => 'ordered',
					'items'   => $this->get_link_list( 'input' ),
				),
				'atts' => array(
					'class' => 'text-indigo-600',
				),
			) ),
			new Controls\Heading_Control( array(
				'id'       => 'overview-checkable-heading',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( '4. Base > Form > Input > Checkable Control', 'affiliatewp-affiliate-portal' ),
					'level'  => 3,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'       => 'overview-checkable-desc',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args'	   => array(
					'text'   => __( 'The Checkable middleware extends Input control for form input controls that support the checked attribute. These extend the Checkable control class:', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\List_Control( array(
				'id'       => 'overview-checkable-list',
				'view_id'  => 'example-controls',
				'section'  => 'example-controls',
				'args' 	   => array(
					'list_type' => 'ordered',
					'items'   => $this->get_link_list( 'checkable' ),
				),
				'atts' => array(
					'class' => 'text-indigo-600',
				),
			) ),
			/**
			* Heading & Paragraph Controls for:
			* 	- Base
			* 	- Form
			* 	- Button
			* 	- Input
			* 	- Checkable
			*/
			new Controls\Heading_Control( array(
				'id'      => 'base-heading',
				'view_id' => 'example-controls',
				'section' => 'base-heading',
				'atts'    => $title_attributes,
				'args'    => array(
					'text'   => __( 'Base Control Examples', 'affiliatewp-affiliate-portal' ),
					'level' => 1,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'      => 'base-desc',
				'view_id' => 'example-controls',
				'section' => 'base-heading',
				'atts'    => $title_attributes,
				'args'     => array(
					'text' => __( 'Descriptive text can go here about base examples.' ),
				),
			) ),
			new Controls\Heading_Control( array(
				'id'      => 'form-heading',
				'view_id' => 'example-controls',
				'section' => 'form-heading',
				'atts'    => $title_attributes,
				'args'    => array(
					'text'   => __( 'Form Control Examples', 'affiliatewp-affiliate-portal' ),
					'level' => 1,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'      => 'form-desc',
				'view_id' => 'example-controls',
				'section' => 'form-heading',
				'atts'    => $title_attributes,
				'args'     => array(
					'text' => __( 'Descriptive text can go here about form examples.' ),
				),
			) ),
			new Controls\Heading_Control( array(
				'id'      => 'button-heading',
				'view_id' => 'example-controls',
				'section' => 'button-heading',
				'atts'    => $title_attributes,
				'args'    => array(
					'text'   => __( 'Button Control Examples', 'affiliatewp-affiliate-portal' ),
					'level' => 1,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'      => 'button-desc',
				'view_id' => 'example-controls',
				'section' => 'button-heading',
				'atts'    => $title_attributes,
				'args'     => array(
					'text' => __( 'Descriptive text can go here about button examples.' ),
				),
			) ),
			new Controls\Heading_Control( array(
				'id'      => 'input-heading',
				'view_id' => 'example-controls',
				'section' => 'input-heading',
				'atts'    => $title_attributes,
				'args'    => array(
					'text'   => __( 'Input Control Examples', 'affiliatewp-affiliate-portal' ),
					'level' => 1,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'      => 'input-desc',
				'view_id' => 'example-controls',
				'section' => 'input-heading',
				'atts'    => $title_attributes,
				'args'     => array(
					'text' => __( 'Descriptive text can go here about input examples.' ),
				),
			) ),
			new Controls\Heading_Control( array(
				'id'      => 'checkable-heading',
				'view_id' => 'example-controls',
				'section' => 'checkable-heading',
				'atts'    => $title_attributes,
				'args'    => array(
					'text'   => __( 'Checkable Control Examples', 'affiliatewp-affiliate-portal' ),
					'level' => 1,
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'      => 'checkable-desc',
				'view_id' => 'example-controls',
				'section' => 'checkable-heading',
				'atts'    => $title_attributes,
				'args'     => array(
					'text' => __( 'Descriptive text can go here about checkable examples.' ),
				),
			) ),
			/**
			* Base extending controls
			*/
			new Controls\Card_Control( array(
				'id'      => 'example-card',
				'view_id' => 'example-controls',
				'section' => 'card-control-section',
				'args'     => array(
					'title'	    => __( 'Example Card', 'affiliatewp-affiliate-portal' ),
					'layout'    => 'info',
					'data'	    => 'Fun fact: Cards can be info or stat layouts. More examples below!',
					'link' 	    => '#card-group-control-section',
					'link_label'  => 'See Card Group Control section',
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'example-card-group-stat',
				'view_id' => 'example-controls',
				'section' => 'card-group-control-section',
				'args'    => array(
					'title'   => __( 'Example Card Group - Stat', 'affiliatewp-affiliate-portal' ),
					'columns' => 3,
					'cards'   => $stat_cards,
				),
			) ),
		    new Controls\Card_Group_Control( array(
		        'id'      => 'resources-card-group-info',
		        'view_id' => 'example-controls',
		        'section' => 'card-group-control-section',
		        'args'    => array(
					'title'   => __( 'Example Card Group - Info', 'affiliatewp-affiliate-portal' ),
					'columns' => 3,
					'cards'   => $info_cards,
				),
		    ) ),
		    // TO DO: add chart control
		    new Controls\Paragraph_Control( array(
				'id'       => 'placement-holder-chart',
				'view_id'  => 'example-controls',
				'section'  => 'chart-control-section',
				'args'	   => array(
					'text'  => __( '[ Coming soon: Chart_Control example ]', 'affiliatewp-affiliate-portal' ),
				),
				'atts'	   => array(
					'class' => 'text-red-800'
				),
			) ),
			new Controls\Code_Block_Control( array(
				'id'      => 'example-code-block',
				'view_id' => 'example-controls',
				'section' => 'code-block-control-section',
				'args' => array(
					'data'      => $example_code_block,
					'show_copy' => false,
				),
			) ),
		    new Controls\Div_With_Copy_Control( array(
				'id'      => 'example-div-with-copy',
				'view_id' => 'example-controls',
				'section' => 'div-with-copy-control-section',
				'args'    => array(
					'label' => __( 'Example #1', 'affiliatewp-affiliate-portal' ),
					'desc'  => __( 'This example sets the copy with the $content param.', 'affiliatewp-affiliate-portal' ),
					'content' => site_url(),
				),
			) ),
			new Controls\Div_With_Copy_Control( array(
				'id'      => 'example-div-with-copy-2',
				'view_id' => 'example-controls',
				'section' => 'div-with-copy-control-section',
				'args'    => array(
					'label' => __( 'Example #2', 'affiliatewp-affiliate-portal' ),
					'desc'  => __( 'This example sets the copy using $get_callback to add the affiliate ID.', 'affiliatewp-affiliate-portal' ),
					'get_callback' => function( $affiliate_id ) {
						$url = site_url();
						$id  = strval( $affiliate_id );
						$url = $url . '?ref=' . $id;

						return $url;
					},
				),
			) ),
			new Controls\Heading_Control( array(
				'id'      => 'example-heading',
				'view_id' => 'example-controls',
				'section' => 'heading-control-section',
				'args'    => array(
					'text'  => __( 'Example h1 heading', 'affiliatewp-affiliate-portal' ),
					'level' => 1,
				),
			) ),
			new Controls\Heading_Control( array(
				'id'      => 'example-heading-2',
				'view_id' => 'example-controls',
				'section' => 'heading-control-section',
				'args'    => array(
					'text'  => __( 'Example h2 heading', 'affiliatewp-affiliate-portal' ),
					'level' => 2,
				),
			) ),
			new Controls\Heading_Control( array(
				'id'      => 'example-heading-3',
				'view_id' => 'example-controls',
				'section' => 'heading-control-section',
				'args'    => array(
					'text'  => __( 'Example h3 heading', 'affiliatewp-affiliate-portal' ),
					'level' => 3,
				),
			) ),
			new Controls\Label_Control( array(
				'id'      => 'example-image-1-label',
				'view_id' => 'example-controls',
				'section' => 'image-control-section',
				'args'    => array(
					'value' => __( 'Example #1', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\Image_Control( array(
				'id'      => 'example-image-1',
				'view_id' => 'example-controls',
				'section' => 'image-control-section',
				'atts'    => array(
					'src'    => 'http://placekitten.com/g/201/201',
					'width'  => 100,
					'height' => 100,
				),
				'args'    => array(
					'desc'  => array(
						'position' => 'before',
						'text'     => __( 'This example displays an image using the $src attribute.', 'affiliatewp-affiliate-portal' ),
					),
				),
			) ),
			new Controls\Label_Control( array(
				'id'      => 'example-image-2-label',
				'view_id' => 'example-controls',
				'section' => 'image-control-section',
				'args'    => array(
					'value' => __( 'Example #2', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\Link_Control( array(
				'id'      => 'example-image-2',
				'view_id' => 'example-controls',
				'section' => 'image-control-section',
				'atts'    => array(
					'href' => '#',
				),
				'args'    => array(
					'image' => new Controls\Image_Control( array(
						'id'      => 'example-image-2',
						'view_id' => 'example-controls',
						'section' => 'image-control-section',
						'atts'    => array(
							'src'    => 'http://placekitten.com/g/201/201',
							'width'  => 100,
							'height' => 100,
						),
						'args'    => array(
							'desc' => array(
								'position' => 'before',
								'text'     => __( 'This example displays an image linked using a Link_Control object.', 'affiliatewp-affiliate-portal' ),
							),
						),
					) ),
				),
			) ),
			new Controls\Icon_Control( array(
				'id'      => 'example-icon',
				'view_id' => 'example-controls',
				'section' => 'icon-control-section',
				'args'    => array(
					'name' => __( 'tag', 'affiliatewp-affiliate-portal' ),
					'size' => '5',
				),
			) ),
			new Controls\Icon_Control( array(
				'id'      => 'example-icon-2',
				'view_id' => 'example-controls',
				'section' => 'icon-control-section',
				'args'    => array(
					'name' => __( 'tag', 'affiliatewp-affiliate-portal' ),
					'size' => '7',				),
				'atts' => array(
					'class' => array( 'text-green-600' ),
				),
			) ),
			new Controls\Icon_Control( array(
				'id'      => 'example-icon-3',
				'view_id' => 'example-controls',
				'section' => 'icon-control-section',
				'args'    => array(
					'name'  => __( 'tag', 'affiliatewp-affiliate-portal' ),
					'size'  => '9',
					'type'  => 'solid',
					'color' => 'indigo-600',
				),
			) ),
			new Controls\Icon_Control( array(
				'id'      => 'example-icon-4',
				'view_id' => 'example-controls',
				'section' => 'icon-control-section',
				'args'    => array(
					'name' => __( 'tag', 'affiliatewp-affiliate-portal' ),
					'size' => '10',
					'type' => 'solid'
				),
				'atts' => array(
					'class' => array( 'text-green-600' ),
				),
			) ),
			new Controls\Label_Control( array(
				'id'      => 'example-label',
				'view_id' => 'example-controls',
				'section' => 'label-control-section',
				'args'    => array(
					'value' => __( 'Just a plain example label', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\Label_Control( array(
				'id'      => 'example-label-2',
				'view_id' => 'example-controls',
				'section' => 'label-control-section',
				'args'    => array(
					'value' => __( 'This label is also a link', 'affiliatewp-affiliate-portal' ),
					'href'  => site_url(),
				),
			) ),
			new Controls\Label_Control( array(
				'id'      => 'example-label-3',
				'view_id' => 'example-controls',
				'section' => 'label-control-section',
				'args'    => array(
					'value' => __( 'Style the label to be your own', 'affiliatewp-affiliate-portal' ),
					'href'  => site_url(),
				),
				'atts'    => array(
					'class' => array(
						'underline',
						'text-green-600',
						'font-bold',
					),
				),
			) ),
			new Controls\Link_Control( array(
				'id'      => 'example-link',
				'view_id' => 'example-controls',
				'section' => 'link-control-section',
				'args'    => array(
					'label'         => __( 'This is a link with an icon after', 'affiliatewp-affiliate-portal' ),
					'icon_position' => 'after',
					'icon'          => new Controls\Icon_Control( array(
						'id'   => 'example-link-icon',
						'args' => array(
							'name' => 'bookmark-alt',
						),
					) ),
				),
				'atts'    => array(
					'href' => '#',
				),
			) ),
			new Controls\Link_Control( array(
				'id'      => 'example-link-2',
				'view_id' => 'example-controls',
				'section' => 'link-control-section',
				'args'    => array(
					'label'         => __( 'This is a link with an icon before', 'affiliatewp-affiliate-portal' ),
					'icon_position' => 'before',
					'icon'          => new Controls\Icon_Control( array(
						'id'   => 'example-link-icon-before',
						'args' => array(
							'name'  => 'bookmark-alt',
							'color' => 'indigo-600',
							'type'  => 'solid',
						),
					) ),
				),
				'atts'    => array(
					'href'  => '#',
					'class' => array(
						'text-green-600',
						'hover:text-green-400',
					),
				),
			) ),
			new Controls\Link_Control( array(
				'id'      => 'example-link-3',
				'view_id' => 'example-controls',
				'section' => 'link-control-section',
				'args'    => array(
					'label'         => __( 'Link with underline', 'affiliatewp-affiliate-portal' ),
					'icon_position' => 'before',
					'icon'          => new Controls\Icon_Control( array(
						'id'   => 'example-link3-icon',
						'args' => array(
							'name'        => 'bookmark-alt',
							'type'        => 'solid',
							'color'       => 'indigo-600',
							'hover_color' => 'green-600',
						),
					) ),
				),
				'atts'    => array(
					'href'  => '#',
					'class' => 'underline',
				),
			) ),
			new Controls\List_Control( array(
				'id'      => 'example-unordered-list',
				'view_id' => 'example-controls',
				'section' => 'list-control-section',
				'args'    => array(
					'list_type' => 'unordered',
					'items'     => array(
						'Up',
						'You',
						'Give',
						'Gonna',
						'Never',
					),
				),
			) ),
			new Controls\List_Control( array(
				'id'      => 'example-ordered-list',
				'view_id' => 'example-controls',
				'section' => 'list-control-section',
				'args'    => array(
					'list_type' => 'ordered',
					'items'     => array(
						'Step 1 - Read the previous list from bottom to top',
						'Step 2 - ...',
						'????',
						'PROFIT!!!',
					),
				),
				'atts'     => array(
					'class' => array(
						'mt-10',
						'mb-20',
					),
				),
			) ),
			new Controls\Paragraph_Control( array(
				'id'      => 'example-paragraph',
				'view_id' => 'example-controls',
				'section' => 'paragraph-control-section',
				'args'     => array(
					'text' => __( 'This is our example Paragraph_Control! Use this to provide more context, convey important information, or to tell a joke! 99 bugs in the code. 99 bugs in the code. Take one down and patch it up. 117 bugs in the code.', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\Table_Control( array(
				'id'      => 'example-table',
				'view_id' => 'example-controls',
				'section' => 'table-control-section',
				'args'    => array(
					'schema' => array(
						'table_name'          => 'example-table',
						'page_count_callback' => function ( $args ) use ( $table_test_data ) {
							$number = isset( $args['number'] ) ? $args['number'] : 20;
							$count  = count( $table_test_data );

							return absint( ceil( $count / $number ) );
						},
						'data_callback'       => function ( $args ) use ( $table_test_data ) {
							return $table_test_data;
						},
						'schema' => array(
							'affiliate'           => array(
								'title'           => __( 'Affiliate', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function ( $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_name", $row['affiliate'] );
								},
							),
							'referral_amount' => array(
								'title'           => __( 'Referral Amount', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function ( $row, $table_control_id ) {
									$amount = affwp_currency_filter( affwp_format_amount( $row['amount'] ) );

									return Controls\Text_Control::create( "{$table_control_id}_amount", $amount );
								},
							),
						),
					),
					'header'  => array(
						'text'   => __( 'Example Stats Table', 'affiliatewp-affiliate-portal' ),
						'level'  => 2,
					),
					'desc'    => 'This table was registered with a <code>Table_Control</code> object. The \'Date\' column has been appended by a separately-registered <code>Table_Column_Control</code> object.',
					'data'    => array(
						'allowSorting' => false,
					),
				),
			) ),
			new Controls\Table_Column_Control( array(
				'id'     => 'example-table-date_column',
				'parent' => 'example-table',
				'args'   => array(
					'title'           => __( 'Date', 'affiliatewp-affiliate-portal' ),
					'render_callback' => function( $row, $table_control_id ) {
						$number = rand( 0, 26 );

						return new Controls\Text_Control( array(
							'id'   => "{$table_control_id}_date",
							'args' => array(
								'text' => affwp_date_i18n( strtotime( "now - {$number} days" ) ),
							),
						) );
					},
				)
			) ),
			new Controls\Wrapper_Control( array(
				'view_id' => 'example-controls',
				'section' => 'wrapper',
			) ),
			/**
			* Form extending controls
			*/
			new Controls\Select_Control( array(
				'id'      => 'example-select',
				'view_id' => 'example-controls',
				'section' => 'select-control-section',
				'args'    => array(
					'posts_data' => false,
					'label'   	 => __( 'Example Select', 'affiliatewp-affiliate-portal' ),
					'options' 	 => array(
						'1' => 'Avocados',
						'2' => 'Bananas',
						'3' => 'Tater tots',
					),
					'selected' 	 => '2',
				),
				'atts'   => array(
					'class' => array(
						'mb-10',
					),
				),
			) ),
			new Controls\Select_Control( array(
				'id'      => 'example-select-2',
				'view_id' => 'example-controls',
				'section' => 'select-control-section',
				'args'     => array(
					'posts_data' => false,
					'label'   	 => __( 'Example Multiple Select', 'affiliatewp-affiliate-portal' ),
					'options'	 => array(
						'1' => 'Avocados',
						'2' => 'Bananas',
						'3' => 'Tater tots',
					),
					'selected' 	 => array(
						'1',
						'3',
					),
				),
				'atts'    => array(
					'multiple' => 'true',
					'class'    => array(
						'mb-10',
					),
				),
			) ),
			new Controls\Textarea_Control( array(
				'id'      => 'example-textarea',
				'view_id' => 'example-controls',
				'section' => 'textarea-control-section',
				'args'     => array(
					'posts_data' => false,
					'label' 	 => __( 'Example Textarea', 'affiliatewp-affiliate-portal' ),
					'desc'  	 => __( 'Describe what text should go in this area AKA the textarea.' )
				),
			) ),
			/**
			* Button - Form extending controls
			*/
			new Controls\Button_Control( array(
				'id'      => 'example-button',
				'view_id' => 'example-controls',
				'section' => 'button-control-section',
				'atts'    => array(
					'value' => __( 'Generic Button', 'affiliatewp-affiliate-portal' ),
				),
				'args'    => array(
					'posts_data' => false,
					'url' 		 => site_url(),
				),
			) ),
			new Controls\Button_Control( array(
				'id'      => 'example-button-2',
				'view_id' => 'example-controls',
				'section' => 'button-control-section',
				'atts'    => array(
					'value'     => __( 'Styled Button', 'affiliatewp-affiliate-portal' ),
					'class'     => array(
						'bg-pink-500',
					),
				),
				'args'    => array(
					'posts_data' => false,
					'url' 		 => site_url(),
					'std_colors' => false,
				),
			) ),
			new Controls\Button_Control( array(
				'id'      => 'example-button-3',
				'view_id' => 'example-controls',
				'section' => 'button-control-section',
				'atts'    => array(
					'value'     => __( 'Disabled Button', 'affiliatewp-affiliate-portal' ),
					'class'     => array(
						'bg-gray-500',
						'hover:bg-gray-100',
					),
					'disabled'    => true,
				),
				'args'    => array(
					'posts_data' => false,
					'url' 		 => site_url(),
					'std_colors' => false,
				),
			) ),
			// TO DO: make this work after other PR is merged
			// new Controls\Copy_Button_Control( array(
			// 	'id'      => 'example-copy-button',
			// 	'view_id' => 'example-controls',
			// 	'section' => 'copy-button-control-section',
			// 	'atts'    => array(
			// 		'value' => __( 'Example Copy Button', 'affiliatewp-affiliate-portal' ),
			// 		'target' => '',
			// 	),
			// ) ),
			new Controls\Paragraph_Control( array(
				'id'       => 'placement-holder-copy-button',
				'view_id'  => 'example-controls',
				'section'  => 'copy-button-control-section',
				'args'	   => array(
					'text'   => __( '[ Coming soon: Copy_Button_Control example ]', 'affiliatewp-affiliate-portal' ),
				),
				'atts'	   => array(
					'class' => 'text-red-800'
				),
			) ),
			new Controls\Submit_Button_Control( array(
				'id'      => 'example-submit-button',
				'view_id' => 'example-controls',
				'section' => 'submit-button-control-section',
				'atts'    => array(
					'value'   => __( 'Example Submit Button', 'affiliatewp-affiliate-portal' ),
				),
				'args'    => array(
					'posts_data' => false,
					'url' 		 => site_url(),
				),
			) ),
			/**
			* Input - Form extending controls
			*/
			new Controls\Date_Control( array(
				'id'      => 'example-date',
				'view_id' => 'example-controls',
				'section' => 'date-control-section',
				'args'    => array(
					'posts_data' => false,
					'label' 	 => __( 'Example Date Input', 'affiliatewp-affiliate-portal' ),
				),
				'atts'    => array(
					'autocomplete' => 'on',
				),
			) ),
			new Controls\Email_Control( array(
				'id'      => 'example-email',
				'view_id' => 'example-controls',
				'section' => 'email-control-section',
				'args'    => array(
					'posts_data' => false,
					'label' 	 => __( 'Example Email Input', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\Hidden_Control( array(
				'id'      => 'example-hidden',
				'view_id' => 'example-controls',
				'section' => 'hidden-control-section',
				'args'    => array(
					'posts_data' => false,
					'label' 	 => __( "You can add hidden inputs like this one. It's there, I promise!  ;)", 'affiliatewp-affiliate-portal' ),
				),
				'atts'    => array(
					'value' => __( "Muahaha! I am the hidden value of this input.", 'affiliatewp-affiliate-portal' )
				),
			) ),
			new Controls\Number_Control( array(
				'id'      => 'example-number',
				'view_id' => 'example-controls',
				'section' => 'number-control-section',
				'args'    => array(
					'posts_data' => false,
					'label' 	 => __( 'Example Number Input', 'affiliatewp-affiliate-portal' ),
				),
				'atts'     => array(
					'value' => 32.02,
					'min'  => 0,
					'max'  => 100,
					'step'  => 0.01,
				),
			) ),
			new Controls\Password_Control( array(
				'id'      => 'example-password',
				'view_id' => 'example-controls',
				'section' => 'password-control-section',
				'args'     => array(
					'posts_data' => false,
					'label' 	 => __( 'Example Password Input', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\Text_Input_Control( array(
				'id'      => 'example-text-input',
				'view_id' => 'example-controls',
				'section' => 'text-input-control-section',
				'args'     => array(
					'posts_data' => false,
					'label' 	 => __( 'Example Text Input', 'affiliatewp-affiliate-portal' ),
				),
			) ),
			new Controls\Checkbox_Control( array(
				'id'      => 'example-checkbox',
				'view_id' => 'example-controls',
				'section' => 'checkbox-control-section',
				'args'     => array(
					'posts_data' => false,
					'label'	  	 => __( 'This is an example checkbox.', 'affiliatewp-affiliate-portal' )
				),
				'atts'     => array(
					'checked' => true,
				),
			) ),
			new Controls\Radio_Control( array(
				'id'      => 'example-radio',
				'view_id' => 'example-controls',
				'section' => 'radio-control-section',
				'args'     => array(
					'posts_data' => false,
					'label' 	 => __( 'Option #1', 'affiliatewp-affiliate-portal' ),
				),
				'atts'    => array(
					'name'  => 'Example Radio',
				),
			) ),
			new Controls\Radio_Control( array(
				'id'      => 'example-radio-2',
				'view_id' => 'example-controls',
				'section' => 'radio-control-section',
				'args'     => array(
					'posts_data' => false,
					'label' 	 => __( 'Option #2', 'affiliatewp-affiliate-portal' ),
				),
				'atts'    => array(
					'name'  => 'Example Radio',
				),
			) ),
			/**
			* Attributes + Arguments info section pt 1: abstract controls
			*/
			new Controls\Heading_Control( array(
				'id'      => 'info-heading',
				'view_id' => 'example-controls',
				'section' => 'atts-args-info',
				'atts'    => $title_attributes,
				'args'    => array(
					'text'   => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'level' => 1,
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'base-info',
				'view_id' => 'example-controls',
				'section' => 'atts-args-info',
				'args'     => array(
					'title'	    => __( 'Base Control (Global)', 'affiliatewp-affiliate-portal' ),
					'columns'    => 2,
					'card_layout' => 'info',
					'cards'      => array(
						array(
							'title' => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data'  => $this->get_atts_list( 'base' )->has_errors() ? '' : $this->get_atts_list( 'base' )->render( false ),
						),
						array(
							'title' => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data'  => $this->get_args_list( 'base' )->has_errors() ? '' : $this->get_args_list( 'base' )->render( false ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'form-info',
				'view_id' => 'example-controls',
				'section' => 'atts-args-info',
				'args'     => array(
					'title'       => __( 'Form Control', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title' => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data'  => $this->get_atts_list( 'form' )->has_errors() ? '' : $this->get_atts_list( 'form' )->render( false ),
						),
						array(
							'title' => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data'  => $this->get_args_list( 'form' )->has_errors() ? '' : $this->get_args_list( 'form' )->render( false ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'button-info-2',
				'view_id' => 'example-controls',
				'section' => 'atts-args-info',
				'args'     => array(
					'title'       => __( 'Button Control', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'      => array(
						array(
							'title' => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data'  => $this->get_atts_list( 'button' )->has_errors() ? '' : $this->get_atts_list( 'button' )->render( false ),
						),
						array(
							'title' => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data'   => $this->get_args_list( 'button' )->has_errors() ? '' : $this->get_args_list( 'button' )->render( false ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'input-info',
				'view_id' => 'example-controls',
				'section' => 'atts-args-info',
				'args'     => array(
					'title'       => __( 'Input Control', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title' => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	   => $this->get_atts_list( 'input' )->has_errors() ? '' : $this->get_atts_list( 'input' )->render( false ),
						),
						array(
							'title' => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	   => $this->get_args_list( 'input' )->has_errors() ? '' : $this->get_args_list( 'input' )->render( false ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'checkable-info',
				'view_id' => 'example-controls',
				'section' => 'atts-args-info',
				'args'     => array(
					'title'       => __( 'Checkable Control', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title' => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data'     => $this->get_atts_list( 'checkable' )->has_errors() ? '' : $this->get_atts_list( 'checkable' )->render( false ),
						),
						array(
							'title' => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	   => $this->get_args_list( 'checkable' )->has_errors() ? '' : $this->get_args_list( 'checkable' )->render( false ),
						),
					),
				),
			) ),
			/**
			* Attributes + Arguments info section pt 2: implemented controls
			*/
			new Controls\Card_Group_Control( array(
				'id'      => 'button-info',
				'view_id' => 'example-controls',
				'section' => 'button-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'button' )->has_errors() ? '' : $this->get_atts_list( 'button' )->render( false ),
							'link' 	     => '#button-info-2-head',
							'link_label' => __( 'See Global + Form + Button Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'button' )->has_errors() ? '' : $this->get_args_list( 'button' )->render( false ),
							'link' 	     => '#button-info-2-head',
							'link_label' => __( 'See Global + Form + Button Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'card-info',
				'view_id' => 'example-controls',
				'section' => 'card-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'card' )->has_errors() ? '' : $this->get_atts_list( 'card' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'card' )->has_errors() ? '' : $this->get_args_list( 'card' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'card-group-info',
				'view_id' => 'example-controls',
				'section' => 'card-group-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'card-group' )->has_errors() ? '' : $this->get_atts_list( 'card-group' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'card-group' )->has_errors() ? '' : $this->get_args_list( 'card-group' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'chart-info',
				'view_id' => 'example-controls',
				'section' => 'chart-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'chart' )->has_errors() ? '' : $this->get_atts_list( 'chart' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'args' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'chart' )->has_errors() ? '' : $this->get_args_list( 'chart' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'checkbox-info',
				'view_id' => 'example-controls',
				'section' => 'checkbox-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'checkbox' )->has_errors() ? '' : $this->get_atts_list( 'checkbox' )->render( false ),
							'link' 	     => '#checkable-info-head',
							'link_label' => __( 'See Global + Form + input + Checkable Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'checkbox' )->has_errors() ? '' : $this->get_args_list( 'checkbox' )->render( false ),
							'link' 	     => '#checkable-info-head',
							'link_label' => __( 'See Global + Form + input + Checkable Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'code-block-info',
				'view_id' => 'example-controls',
				'section' => 'code-block-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'code-block' )->has_errors() ? '' : $this->get_atts_list( 'code-block' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'code-block' )->has_errors() ? '' : $this->get_args_list( 'code-block' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'copy-button-info',
				'view_id' => 'example-controls',
				'section' => 'copy-button-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'copy-button' )->has_errors() ? '' : $this->get_atts_list( 'copy-button' )->render( false ),
							'link' 	     => '#button-info-2-head',
							'link_label' => __( 'See Global + Form + Button Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'copy-button' )->has_errors() ? '' : $this->get_args_list( 'copy-button' )->render( false ),
							'link' 	     => '#button-info-2-head',
							'link_label' => __( 'See Global + Form + Button Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'date-info',
				'view_id' => 'example-controls',
				'section' => 'date-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'date' )->has_errors() ? '' : $this->get_atts_list( 'date' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'date' )->has_errors() ? '' : $this->get_args_list( 'date' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'div-with-copy-info',
				'view_id' => 'example-controls',
				'section' => 'div-with-copy-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'div-with-copy' )->has_errors() ? '' : $this->get_atts_list( 'div-with-copy' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'div-with-copy' )->has_errors() ? '' : $this->get_args_list( 'div-with-copy' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'email-info',
				'view_id' => 'example-controls',
				'section' => 'email-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'email' )->has_errors() ? '' : $this->get_atts_list( 'email' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'email' )->has_errors() ? '' : $this->get_args_list( 'email' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'heading-info',
				'view_id' => 'example-controls',
				'section' => 'heading-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'heading' )->has_errors() ? '' : $this->get_atts_list( 'heading' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'heading' )->has_errors() ? '' : $this->get_args_list( 'heading' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'hidden-info',
				'view_id' => 'example-controls',
				'section' => 'hidden-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'hidden' )->has_errors() ? '' : $this->get_atts_list( 'hidden' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'hidden' )->has_errors() ? '' : $this->get_args_list( 'hidden' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'image-info',
				'view_id' => 'example-controls',
				'section' => 'image-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'image' )->has_errors() ? '' : $this->get_atts_list( 'image' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'image' )->has_errors() ? '' : $this->get_args_list( 'image' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'icon-info',
				'view_id' => 'example-controls',
				'section' => 'icon-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'icon' )->has_errors() ? '' : $this->get_atts_list( 'icon' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'icon' )->has_errors() ? '' : $this->get_args_list( 'icon' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'label-info',
				'view_id' => 'example-controls',
				'section' => 'label-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'label' )->has_errors() ? '' : $this->get_atts_list( 'label' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'label' )->has_errors() ? '' : $this->get_args_list( 'label' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'link-info',
				'view_id' => 'example-controls',
				'section' => 'link-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'link' )->has_errors() ? '' : $this->get_atts_list( 'link' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'link' )->has_errors() ? '' : $this->get_args_list( 'link' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'list-info',
				'view_id' => 'example-controls',
				'section' => 'list-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'list' )->has_errors() ? '' : $this->get_atts_list( 'list' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'list' )->has_errors() ? '' : $this->get_args_list( 'list' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'number-info',
				'view_id' => 'example-controls',
				'section' => 'number-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'number' )->has_errors() ? '' : $this->get_atts_list( 'number' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'number' )->has_errors() ? '' : $this->get_args_list( 'number' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'paragraph-info',
				'view_id' => 'example-controls',
				'section' => 'paragraph-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'paragraph' )->has_errors() ? '' : $this->get_atts_list( 'paragraph' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'paragraph' )->has_errors() ? '' : $this->get_args_list( 'paragraph' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'password-info',
				'view_id' => 'example-controls',
				'section' => 'password-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'password' )->has_errors() ? '' : $this->get_atts_list( 'password' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'password' )->has_errors() ? '' : $this->get_args_list( 'password' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'radio-info',
				'view_id' => 'example-controls',
				'section' => 'radio-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'radio' )->has_errors() ? '' : $this->get_atts_list( 'radio' )->render( false ),
							'link' 	     => '#checkable-info-head',
							'link_label' => __( 'See Global + Form + input + Checkable Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'radio' )->has_errors() ? '' : $this->get_args_list( 'radio' )->render( false ),
							'link' 	     => '#checkable-info-head',
							'link_label' => __( 'See Global + Form + input + Checkable Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'select-info',
				'view_id' => 'example-controls',
				'section' => 'select-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'select' )->has_errors() ? '' : $this->get_atts_list( 'select' )->render( false ),
							'link' 	     => '#form-info-head',
							'link_label' => __( 'See Global + Form Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'select' )->has_errors() ? '' : $this->get_args_list( 'select' )->render( false ),
							'link' 	     => '#form-info-head',
							'link_label' => __( 'See Global + Form Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'submit-button-info',
				'view_id' => 'example-controls',
				'section' => 'submit-button-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'submit-button' )->has_errors() ? '' : $this->get_atts_list( 'submit-button' )->render( false ),
							'link' 	     => '#button-info-2-head',
							'link_label' => __( 'See Global + Form + Button Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'submit-button' )->has_errors() ? '' : $this->get_args_list( 'submit-button' )->render( false ),
							'link' 	     => '#button-info-2-head',
							'link_label' => __( 'See Global + Form + Button Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'table-info',
				'view_id' => 'example-controls',
				'section' => 'table-control-section',
				'args'     => array(
					'title'       => __( 'Table Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'table' )->has_errors() ? '' : $this->get_atts_list( 'table' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'table' )->has_errors() ? '' : $this->get_args_list( 'table' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'table-column-info',
				'view_id' => 'example-controls',
				'section' => 'table-control-section',
				'args'     => array(
					'title'       => __( 'Table Column Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'table-column' )->has_errors() ? '' : $this->get_atts_list( 'table-column' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'table-column' )->has_errors() ? '' : $this->get_args_list( 'table-column' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'text-input-info',
				'view_id' => 'example-controls',
				'section' => 'text-input-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'text-input' )->has_errors() ? '' : $this->get_atts_list( 'text-input' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'text-input' )->has_errors() ? '' : $this->get_args_list( 'text-input' )->render( false ),
							'link' 	     => '#input-info-head',
							'link_label' => __( 'See Global + Form + Input Options' , 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'textarea-info',
				'view_id' => 'example-controls',
				'section' => 'textarea-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'textarea' )->has_errors() ? '' : $this->get_atts_list( 'textarea' )->render( false ),
							'link' 	     => '#form-info-head',
							'link_label' => __( 'See Global + Form Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'textarea' )->has_errors() ? '' : $this->get_args_list( 'textarea' )->render( false ),
							'link' 	     => '#form-info-head',
							'link_label' => __( 'See Global + Form Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'wrapper-info',
				'view_id' => 'example-controls',
				'section' => 'wrapper-control-section',
				'args'     => array(
					'title'       => __( 'Control Options', 'affiliatewp-affiliate-portal' ),
					'columns'     => 2,
					'card_layout' => 'info',
					'cards'       => array(
						array(
							'title'      => array(
								'text' => __( 'Attributes', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_atts_list( 'wrapper' )->has_errors() ? '' : $this->get_atts_list( 'wrapper' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
						array(
							'title'      => array(
								'text' => __( 'Arguments', 'affiliatewp-affiliate-portal' ),
								'atts' => $title_attributes,
							),
							'data' 	     => $this->get_args_list( 'wrapper' )->has_errors() ? '' : $this->get_args_list( 'wrapper' )->render( false ),
							'link' 	     => '#base-info-head',
							'link_label' => __( 'See Global Options', 'affiliatewp-affiliate-portal' ),
						),
					),
				),
			) ),
		);
	}

	/**
	 * Overview section list data for base, form, button, input, and checkable.
	 *
	 * @since 1.0.0
	 *
	 * @param string $list_section Specifiy which section's list (base, form, button, input, or checkable)
	 * @return array[] List items - 'name' for the list item and 'id' for the section id
	 */
	private function overview_list_data( $list_section ) {
		$overview_lists = array(
			'base' => array(
				array(
					'name'  => 'Card_Control',
					'id'  => 'card-control-section',
				),
				array(
					'name'  => 'Card_Group_Control',
					'id'  => 'card-group-control-section',
				),
				array(
					'name'  => 'Chart_Control',
					'id'  => 'chart-control-section',
				),
				array(
					'name'  => 'Code_Block_Control',
					'id'  => 'code-block-control-section',
				),
				array(
					'name'  => 'Div_With_Copy_Control',
					'id'  => 'div-with-copy-control-section',
				),
				array(
					'name'  => 'Heading_Control',
					'id'  => 'heading-control-section',
				),
				array(
					'name' => 'Image_Control',
					'id'   => 'image-control-section',
				),
				array(
					'name'  => 'Icon_Control',
					'id'  => 'icon-control-section',
				),
				array(
					'name'  => 'Label_Control',
					'id'  => 'label-control-section',
				),
				array(
					'name'  => 'Link_Control',
					'id'  => 'link-control-section',
				),
				array(
					'name'  => 'List_Control',
					'id'  => 'list-control-section',
				),
				array(
					'name'  => 'Paragraph_Control',
					'id'  => 'paragraph-control-section',
				),
				array(
					'name'  => 'Table_Column_Control',
					'id'  => 'table-control-section',
				),
				array(
					'name'  => 'Table_Control',
					'id'  => 'table-control-section',
				),
				array(
					'name'  => 'Wrapper_Control',
					'id'  => 'wrapper-control-section',
				),
			),
			'form' => array(
				array(
					'name'  => 'Select_Control',
					'id'  => 'select-control-section',
				),
				array(
					'name'  => 'Textarea_Control',
					'id'  => 'textarea-control-section',
				),
			),
			'button' => array(
				array(
					'name'  => 'Button_Control',
					'id'  => 'button-control-section',
				),
				array(
					'name'  => 'Copy_Button_Control',
					'id'  => 'copy-button-control-section',
				),
				array(
					'name'  => 'Submit_Button_Control',
					'id'  => 'submit-button-control-section',
				),
			),
			'input' => array(
				array(
					'name'  => 'Date_Control',
					'id'  => 'date-control-section',
				),
				array(
					'name'  => 'Email_Control',
					'id'  => 'email-control-section',
				),
				array(
					'name'  => 'Hidden_Control',
					'id'  => 'hidden-control-section',
				),
				array(
					'name'  => 'Number_Control',
					'id'  => 'number-control-section',
				),
				array(
					'name'  => 'Password_Control',
					'id'  => 'password-control-section',
				),
				array(
					'name'  => 'Text_Input_Control',
					'id'  => 'text-input-control-section',
				),
			),
			'checkable' => array(
				array(
					'name'  => 'Checkbox_Control',
					'id'  => 'checkbox-control-section',
				),
				array(
					'name'  => 'Radio_Control',
					'id'  => 'radio-control-section',
				),
			),
		);
		return $overview_lists[$list_section];
	}

	/**
	 * Create the link lists for the overview sections
	 *
	 * @since 1.0.0
	 *
	 * @param string $list_section Specifiy which section's list (base, form, button, input, or checkable)
	 * @return array[] List of anchor links for that section
	 */
	private function get_link_list( $list_section ) {

		$link_list = array_map( array( $this, 'build_anchor_link' ),
			$this->overview_list_data( $list_section )
		);

		return $link_list;

	}

	/**
	 * Build an anchor link to a id on this page
	 *
	 * @since 1.0.0
	 *
	 * @param array[] $link Array with 'name' for the label and 'id' for the href.
	 * @return string Anchor link.
	 */
	public function build_anchor_link( $link ) {

	    $link_control = new Controls\Link_Control( array(
	        'id'   => $link['id'] . '-link',
	        'args' => array(
	            'label' => $link['name'],
	        ),
	        'atts' => array(
	        	'href'  => '#' . $link['id'],
	        ),
	    ) );
	    if ( ! $link_control->has_errors() ) {
	        $output = $link_control->render( false );
	    } else {
	        $output = '';
	    }
	    return $output;

	}

	/**
	 * Retrieves atts/args whitelists based on control type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_type Control type.
	 * @return array[] Atts/args whitelists for the given control type.
	 */
	private function control_whitelist_data( $control_type ) {
		switch ( $control_type ) {
			case 'base':
				$data = array(
					'atts' => array( 'id', 'class', 'alpine', 'data' ),
					'args' => array( 'n/a' )
				);
				break;

			case 'button':
				$data = array(
					'atts' => array( 'value' ),
					'args' => array( 'value_atts', 'url', 'std_classes' )
				);
				break;

			case 'card':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array(
						'data',
						'data_key',
						'icon',
						'title',
						'link',
						'link_label',
						'link_target',
						'compare',
						'comparison',
						'format',
						'layout'
					)
				);
				break;

			case 'card-group':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'cards', 'columns', 'title', )
				);
				break;

			// TO DO: add these
			case 'chart':
				$data = array(
					'atts' => array( 'coming soon.' ),
					'args' => array( 'coming soon.' )
				);
				break;

			case 'code-block':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'data', 'data_key', 'show_copy', )
				);
				break;

			case 'checkable':
				$data = array(
					'atts' => array( 'checked' ),
					'args' => array( 'desc', 'label_class', 'label_href', 'label_href_class', 'label' )
				);
				break;

			case 'checkbox':
				$data = array(
					'atts' => array( 'checked' ),
					'args' => array( 'n/a' )
				);
				break;

			case 'copy-button':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'target' )
				);
				break;

			case 'div-with-copy':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'desc', 'label', 'label_class', 'content', 'copy_button_alpine' )
				);
				break;

			case 'form':
				$data = array(
					'atts' => array(
						'name - Hardcoded to the control ID for all but hidden controls',
						'aria',
						'readonly',
						'disabled'
					),
					'args' => array( 'label', 'label_class' )
				);
				break;

			case 'heading':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'text', 'level' )
				);
				break;

			case 'hidden':
				$data = array(
					'atts' => array( 'value' ),
					'args' => array( 'n/a' )
				);
				break;

			case 'image':
				$data = array(
					'atts' => array( 'src', 'srcset', 'height', 'width' ),
					'args' => array( 'desc', 'get_callback' ),
				);
				break;

			case 'icon':
				$data = array(
					'atts' => array( 'stroke' ),
					'args' => array( 'name', 'type', 'size', 'path', 'color', 'hover_color' )
				);
				break;

			case 'input':
				$data = array(
					'atts' => array( 'placeholder', 'autocomplete', 'type' ),
					'args' => array( 'desc', 'error', 'label_href', 'label_href_class' )
				);
				break;

			case 'label':
				$data = array(
					'atts' => array( 'for' ),
					'args' => array( 'href', 'href_class', 'value' )
				);
				break;

			case 'link':
				$data = array(
					'atts' => array( 'href' ),
					'args' => array( 'icon', 'icon_position', 'label' )
				);
				break;

			case 'list':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'list_type', 'items' )
				);
				break;

			case 'number':
				$data = array(
					'atts' => array( 'value', 'min', 'max', 'step' ),
					'args' => array( 'n/a' )
				);
				break;

			case 'paragraph':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'text' )
				);
				break;

			case 'radio':
				$data = array(
					'atts' => array( 'checked' ),
					'args' => array( 'n/a' )
				);
				break;

			case 'select':
				$data = array(
					'atts' => array( 'multiple' ),
					'args' => array( 'desc', 'options', 'label', 'label_class', 'selected' )
				);
				break;

			case 'submit-button':
				$data = array(
					'atts' => array( 'value' ),
					'args' => array( 'value_atts' )
				);
				break;

			case 'table':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'data', 'header', 'desc', 'schema[ title, cell, priority ]' )
				);
				break;

			case 'table-column':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'title', 'render_callback', 'priority' )
				);
				break;

			case 'text-input':
				$data = array(
					'atts' => array( 'value' ),
					'args' => array( 'n/a' )
				);
				break;

			case 'textarea':
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'content', 'desc', 'label' )
				);
				break;

			case 'date':
			case 'email':
			case 'password':
			case 'wrapper':
			default:
				$data = array(
					'atts' => array( 'n/a' ),
					'args' => array( 'n/a' )
				);
				break;
		}

		return $data;
	}

	/**
	 * Create a list of attributes for a control
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_type Control type.
	 * @return Controls\List_Control List_Control object containing the attributes.
	 */
	private function get_atts_list( $control_type ) {

		$data = $this->control_whitelist_data( $control_type );

		$card_atts_list = new Controls\List_Control( array(
			'id'        => 'card-atts-list-' . $control_type,
			'list_type' => 'unordered',
			'args'	    => array(
				'items' => $data['atts'],
			),
		) );

		return $card_atts_list;
	}

	/**
	 * Create a list of arguments for a control
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_type Control type.
	 * @return Controls\List_Control List_Control object containing the attributes.
	 */
	private function get_args_list( $control_type ) {

		$data = $this->control_whitelist_data( $control_type );

		$card_args_list = new Controls\List_Control( array(
			'id'     => 'card-args-list-' . $control_type,
			'list_type' => 'unordered',
			'args'	   => array(
				'items'   => $data['args'],
			),
		) );

		return $card_args_list;
	}

}
