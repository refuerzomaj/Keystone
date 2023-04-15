<?php
namespace Essential\Restate\Common\Currencies;

use Essential\Restate\Traitval\Traitval;

/**
 * The admin class
 */
class Currencies {

	use Traitval;

	public static function cl_admin_get_currencies() {
		$currencies = array(
			'USD'  => __( 'US Dollars (&#36;)', 'essential-wp-real-estate' ),
			'EUR'  => __( 'Euros (&euro;)', 'essential-wp-real-estate' ),
			'GBP'  => __( 'Pound Sterling (&pound;)', 'essential-wp-real-estate' ),
			'AUD'  => __( 'Australian Dollars (&#36;)', 'essential-wp-real-estate' ),
			'BRL'  => __( 'Brazilian Real (R&#36;)', 'essential-wp-real-estate' ),
			'CAD'  => __( 'Canadian Dollars (&#36;)', 'essential-wp-real-estate' ),
			'CZK'  => __( 'Czech Koruna', 'essential-wp-real-estate' ),
			'DKK'  => __( 'Danish Krone', 'essential-wp-real-estate' ),
			'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'essential-wp-real-estate' ),
			'HUF'  => __( 'Hungarian Forint', 'essential-wp-real-estate' ),
			'ILS'  => __( 'Israeli Shekel (&#8362;)', 'essential-wp-real-estate' ),
			'JPY'  => __( 'Japanese Yen (&yen;)', 'essential-wp-real-estate' ),
			'MYR'  => __( 'Malaysian Ringgits', 'essential-wp-real-estate' ),
			'MXN'  => __( 'Mexican Peso (&#36;)', 'essential-wp-real-estate' ),
			'NZD'  => __( 'New Zealand Dollar (&#36;)', 'essential-wp-real-estate' ),
			'NOK'  => __( 'Norwegian Krone', 'essential-wp-real-estate' ),
			'PHP'  => __( 'Philippine Pesos', 'essential-wp-real-estate' ),
			'PLN'  => __( 'Polish Zloty', 'essential-wp-real-estate' ),
			'SGD'  => __( 'Singapore Dollar (&#36;)', 'essential-wp-real-estate' ),
			'SEK'  => __( 'Swedish Krona', 'essential-wp-real-estate' ),
			'CHF'  => __( 'Swiss Franc', 'essential-wp-real-estate' ),
			'TWD'  => __( 'Taiwan New Dollars', 'essential-wp-real-estate' ),
			'THB'  => __( 'Thai Baht (&#3647;)', 'essential-wp-real-estate' ),
			'INR'  => __( 'Indian Rupee (&#8377;)', 'essential-wp-real-estate' ),
			'TRY'  => __( 'Turkish Lira (&#8378;)', 'essential-wp-real-estate' ),
			'RIAL' => __( 'Iranian Rial (&#65020;)', 'essential-wp-real-estate' ),
			'RUB'  => __( 'Russian Rubles', 'essential-wp-real-estate' ),
			'AOA'  => __( 'Angolan Kwanza', 'essential-wp-real-estate' ),
			'AED'  => __( 'United Arab Emirates dirham (د.إ)', 'essential-wp-real-estate' ),
			'KES'  => __( 'Kenyan shilling (KES)', 'essential-wp-real-estate' ),
			'NGN'  => __( 'Nigerian naira (₦)', 'essential-wp-real-estate' ),
			'GTQ'  => __( 'Guatemalan quetzal (Q)', 'essential-wp-real-estate' ),
			'PKR'  => __( 'Pakistani Rupee (PKR)', 'essential-wp-real-estate' ),
			'BGN'  => __( 'Bulgarian lev (BGN)', 'essential-wp-real-estate' ),
		);

		return apply_filters( 'cl_admin_currencies', $currencies );
	}

	function cl_currency_symbol( $currency = '' ) {
		if ( empty( $currency ) ) {
			$currency = WPERECCP()->common->options->cl_get_currency();
		}

		switch ( $currency ) :
			case 'GBP':
				$symbol = '&pound;';
				break;
			case 'BRL':
				$symbol = 'R&#36;';
				break;
			case 'EUR':
				$symbol = '&euro;';
				break;
			case 'USD':
			case 'AUD':
			case 'NZD':
			case 'CAD':
			case 'HKD':
			case 'MXN':
			case 'SGD':
				$symbol = '&#36;';
				break;
			case 'JPY':
				$symbol = '&yen;';
				break;
			case 'THB':
				$symbol = '&#3647;';
				break;
			case 'TRY':
				$symbol = '₺';
				break;
			case 'AOA':
				$symbol = 'Kz';
				break;
			case 'AED':
				$symbol = 'د.إ';
				break;
			case 'KES':
				$symbol = 'KES';
				break;
			case 'NGN':
				$symbol = '₦';
				break;
			case 'GTQ':
				$symbol = 'Q';
				break;
			case 'BGN':
				$symbol = 'Лв';
				break;
			case 'INR':
				$symbol = '₹';
				break;
			default:
				$symbol = $currency;
				break;
		endswitch;

		return apply_filters( 'cl_currency_symbol', $symbol, $currency );
	}
}
