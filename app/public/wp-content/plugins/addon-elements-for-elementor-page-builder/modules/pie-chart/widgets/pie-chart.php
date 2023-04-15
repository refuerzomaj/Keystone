<?php
namespace WTS_EAE\Modules\PieChart\Widgets;


use WTS_EAE\Base\EAE_Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;




if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PieChart extends EAE_Widget_Base{

	public function get_name() {
		return 'wts-pie-chart';
	}
	public function get_title() {
		return __( 'EAE - Pie Chart', 'wts-eae' );
	}

	public function get_icon() {
		return '';
	}

	public function get_categories() {
		return [ 'wts-eae' ];
	}

	public function get_script_depends() {
		return [ 'eae-chart' ];
	}

	protected function register_controls() {
		$this->layout();
	}

		function layout(){

			$this->start_controls_section(
				'layout_section',
				[
					'label' => __( 'Layout', 'wts-eae' ),
				]
			);

			// $this->add_control(
			// 	'effect',
			// 	[
			// 		'label'   => esc_html__( 'Skin', 'wts-eae' ),
			// 		'type'    => Controls_Manager::SELECT,
			// 		'options' => [
			// 			'pie_chart'  => 'Pie Chart',
			// 			'doughnut' => 'Doughnut',
			// 		],
			// 		'default' => 'pie_chart', 
			// 		'separator' => 'after',   
			// 	]
			// );
			$this->add_control(
				'chart_labels',
				[
					'label'       => __( 'Labels', 'wts-eae' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => '2017',
					// 'placeholder' => __( 'January, February, March', 'wts-eae' ),
					// 'description' => __( 'Enter multiple Labels Separated With ( , ). Example: January, February, March etc. ', 'wts-eae' ),
				]
			);

			
			$repeater = new Repeater();

			$repeater->start_controls_tabs( 'start_chart_data_tab' );

			$repeater->start_controls_tab(
				'pie_content',
				[
					'label' => __( 'Content', 'wts-eae' ),
				]
			);

			$repeater->add_control(
				'chart_label',
				[
					'label'       => __( 'Label', 'wts-eae' ),
					'label_block' => true,
					'type'        => Controls_Manager::TEXT,
					'default'     => __( 'Lable', 'wts-eae' ),
					'placeholder' => __( 'Enter your label', 'wts-eae' ),
				]
			);

			$repeater->add_control(
				'chart_data',
				[
					'label'       => __('Value', 'wts-eae' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => '',
					'placeholder' => __( '', 'wts-eae' ),
					// 'description' => __( 'Enter Data Values Separated With ( , ). Example: 10, 20, 30 ', 'wts-eae' ),
				]
			);
			
			$repeater->end_controls_tab();
			$repeater->start_controls_tab(
				'style',
				[
					'label' => __( 'Style', 'wts-eae' ),
				]
			);

			$repeater->add_control(
				'chart_background_color',
				[
					'label'   => __( 'Background Color', 'wts-eae' ),
					'type'    => Controls_Manager::COLOR,
					'default' => '#52C0C0BA',
				]
			);

			$repeater->add_control(
				'chart_background_hover_color',
				[
					'label'   => __( 'Background Hover Color', 'wts-eae' ),
					'type'    => Controls_Manager::COLOR,
					'default' => '#52C0C0E0',
				]
			);

			$repeater->add_control(
				'chart_border_color',
				[
					'label'   => __( 'Border Color', 'wts-eae' ),
					'type'    => Controls_Manager::COLOR,
					'default' => '#49C4C4',
				]
			);

			$repeater->add_control(
				'chart_border_hover_color',
				[
					'label'   => __( 'Border Hover Color', 'wts-eae' ),
					'type'    => Controls_Manager::COLOR,
					'default' => '#49C4C4',
				]
			);
			$repeater->end_controls_tab();
			// $repeater->end_controls_tabs();

			$this->add_control(
				'dataset',
				[
					'label'       => __( '', 'wts-eae' ),
					'type'        => Controls_Manager::REPEATER,
					'show_label'  => true,
					'fields'      => $repeater->get_controls(),
					'default'     => [
						[
							'chart_label'                => __( 'Google', 'wts-eae' ),
							'chart_data'                 => '30',
							'chart_background_color'             => '#dd4b39',
							'chart_background_hover_color'       => '#EE6083D9',
							'chart_border_color'                 => '#F34A74',
							'chart_border_hover_color'           => '#F34A74',
						],
						[
							'chart_label'                => __( 'Facebook', 'wts-eae' ),
							'chart_data'                 => '37',
							'chart_background_color'             => '#3b5998',
							'chart_background_hover_color'       => '#3BA3ECDE',
							'chart_border_color'                 => '#2999E8',
							'chart_border_hover_color'           => '#2999E8',
						],
						[
							'chart_label'                => __( 'Twitter', 'wts-eae' ),
							'chart_data'                 => '77',
							'chart_background_color'             => '#55acee',
							'chart_background_hover_color'       => '#3BA3ECDE',
							'chart_border_color'                 => '#2999E8',
							'chart_border_hover_color'           => '#2999E8',
						],
						[
							'chart_label'                => __( 'Instagram', 'wts-eae' ),
							'chart_data'                 => '65',
							'chart_background_color'             => '#0E293E',
							'chart_background_hover_color'       => '#3BA3ECDE',
							'chart_border_color'                 => '#2999E8',
							'chart_border_hover_color'           => '#2999E8',
						],
					],
					'title_field' => '{{{ chart_label }}}',
				]
			);
			

			$this->end_controls_section();
		
			$this->start_controls_section(
				'section_additional_settings',
				[
					'label' => __( 'Additional Settings', 'wts-eae' ),
				]
			);
			$this->add_responsive_control(
				'eae_chart_pie_height',
				[
					'label'     => __( 'Chart Height', 'wts-eae' ),
					'type'      => Controls_Manager::NUMBER,
					'min'       => 50,
					'max'       => 1500,
					'step'      => 5,
					'default'   => 500,
					// 'selectors' => [
						// '{{WRAPPER}} ' => 'height: {{SIZE}}px;',
						// '{{WRAPPER}} ' => 'height: {{SIZE}}px !important;',
					// ],
				]
			);
			$this->add_responsive_control(
				'chart_cutout',
				[
					'label' => esc_html__( 'Icon Size', 'wts-eae' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' =>'%',
					'range' => [
						'%' => [
							'min' => 0,
							'max' => 99,
						],
					],
				]
			);

			$this->add_control(
				'chart_title',
				[
					'label'     => __( 'Title', 'wts-eae' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
	
			$this->add_control(
				'chart_title_display',
				[
					'label'        => __( 'Enable Title', 'wts-eae' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'wts-eae' ),
					'label_off'    => __( 'No', 'wts-eae' ),
					'return_value' => 'yes',
				]
			);
	
			$this->add_control(
				'chart_pie_title',
				[
					'label'       => __( 'Title', 'wts-eae' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => 'Add Your Heading',
					'placeholder' => __( 'Enter Title', 'wts-eae' ),
					'condition'   => [
						'chart_title_display' => 'yes',
					],
				]
			);
	
			$this->add_control(
				'chart_title_position',
				[
					'type'      => Controls_Manager::SELECT,
					'label'     => __( 'Position', 'wts-eae' ),
					'default'   => 'top',
					'options'   => [
						'top'    => __( 'Top', 'wts-eae' ),
						'bottom' => __( 'Bottom', 'wts-eae' ),
					],
					'condition' => [
						'chart_title_display' => 'yes',
					],
	
				]
			);
			$this->add_control(
				'chart_legend',
				[
					'label'     => __( 'Legend', 'wts-eae' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
	
			$this->add_control(
				'chart_legend_display',
				[
					'label'        => __( 'Enable Legend', 'wts-eae' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'label_on'     => 'Yes',
					'label_off'    => 'No',
					'return_value' => 'yes',
				]
			);
	
			$this->add_control(
				'chart_legend_position',
				[
					'label'     => __( 'Position', 'wts-eae' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => [
						'top'    => __( 'Top', 'wts-eae' ),
						'left'   => __( 'Left', 'wts-eae' ),
						'bottom' => __( 'Bottom', 'wts-eae' ),
						'right'  => __( 'Right', 'wts-eae' ),
					],
					'default'   => 'top',
					'condition' => [
						'chart_legend_display' => 'yes',
					],
				]
			);
	
			$this->add_control(
				'chart_legend_align',
				[
					'label'     => __( 'Alignment', 'wts-eae' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => [
						'start' => [
							'title' => __( 'Left', 'wts-eae' ),
							'icon'  => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'wts-eae' ),
							'icon'  => 'eicon-text-align-center',
						],
						'end' => [
							'title' => __( 'Right', 'wts-eae' ),
							'icon'  => 'eicon-text-align-right',
						],
					],
					'default'   => 'center',
					'condition' => [
						'chart_legend_display' => 'yes',
					],
				]
			);
	
			$this->add_control(
				'chart_legend_reverse',
				[
					'label'        => __( 'Reverse', 'wts-eae' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'no',
					'label_on'     => 'Yes',
					'label_off'    => 'No',
					'return_value' => 'yes',
					'condition'    => [
						'legend_display' => 'yes',
					],
				]
			);

		$this->add_control(
			'chart_tooltip',
			[
				'label'     => __( 'Tooltip', 'wts-eae' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'chart_tooltip_display',
			[
				'label'        => __( 'Enable Tooltips', 'wts-eae' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

			$this->end_controls_section();

	}
	protected function render() {
		$settings= $this->get_settings_for_display();
		// echo "<pre>"; print_r( $settings); echo "</pre>";

		$settings     = $this->get_settings_for_display();
		$data_chart   = $this->get_chart_data();
				// echo "<pre>"; print_r($data_chart); echo "</pre>";

		$this->add_render_attribute( 'container', 'class', 'eae-pie-chart-container' );
		$this->add_render_attribute( 'container', 'data-chart', json_encode($data_chart));

		$this->add_render_attribute( 'canvas', 'id', 'eae-pie-chart' );
		
		?>


		<div <?php $this->print_render_attribute_string( 'container' ); ?>>
			<canvas <?php $this->print_render_attribute_string( 'canvas' ); ?>></canvas>
		</div>
		 
		<?php
		// $data_options = $this->get_chart_options();
	}
	

	public function get_chart_data() {

		$settings = $this->get_settings_for_display();

	    // echo "<pre>"; print_r($settings); echo "</pre>";

		$chart_data = [];
		$chart=[];
	
	    $chart_data = $settings['dataset'];
	// echo "<pre>"; print_r($settings['dataset']); echo "</pre>";

	foreach ( $chart_data as $item_data ) {
	$data['labels'][]  = ! empty( $item_data['chart_label'] ) ? $item_data['chart_label'] : '';
	$data['datasets'][0]['label'] =! empty($settings['chart_labels']) ? $settings['chart_labels'] : '';
	$data['datasets'][0]['data'][]   = ! empty( $item_data['chart_data'] ) ? $item_data['chart_data'] : '';
	$data['datasets'][0]['backgroundColor'][] = ! empty( $item_data['chart_background_color'] ) ? $item_data['chart_background_color'] : '';
	$data['datasets'][0]['cutout'] = ! empty( $settings['chart_cutout']['size'] ) ? $settings['chart_cutout'] : '';
		// echo $item_data['chart_labels']; 
	}
	// echo "<pre>"; print_r($data); echo "</pre>";
	$chart = (
					[
							'type'    => 'pie',
							'data'    =>$data,
							//  [
							// 	'label'   => explode(',',$settings['chart_labels']),
							// 	 'data' => $data,
							// ],
					]
			);			
			//echo "<pre>"; print_r($chart); echo "</pre>";
			return $chart;
	}






}