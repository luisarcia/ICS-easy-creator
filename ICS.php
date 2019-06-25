<?php
/**
 * ICS Easy Creator
 * @author Luis Arcia
 * @version 1.0
 * @since 2019
 * Weblarc.com
 */


class ICS
{
	const FDT = 'Ymd\THis';
	public $timeZone;
	private $param;

	private $available_props = [
		'summary',
		'description',
		'dtstart',
		'dtend'
	];

	/**
	* Establece la zona horaria
	* @param String $timeZone Zona horaria
	*/
	public function __construct( $timeZone )
	{
		$this->timeZone = $timeZone;
	}

	/**
	* Establece los parametros y valida que se un Array
	* @param Array $param Datos del recordatorio
	*/
	public function set( $param )
	{
		$this->param = is_array( $param ) ? $param : null;
	}

	/**
	* Contruye el ICS con sus parametros
	* @return String Texto del archivo
	*/
	public function build()
	{
		if( !is_null( $this->param ) ) {
			$ics_file = array(
				'BEGIN:VCALENDAR',
				'VERSION:2.0',
				'PRODID:-//Movistar Panama 2019//MyTools//ES',
				'CALSCALE:GREGORIAN'
			);

			for ($i=0; $i < count( $this->param ); $i++) {
				$ics_file[] = 'BEGIN:VEVENT';

				foreach( $this->param[$i] as $key => $value ) {
					if( in_array( $key, $this->available_props) ) {
						if( $key == 'dtstart' || $key == 'dtend' ) {
							$ics_file[] = strtoupper( $key ).';TZID='.$this->timeZone.':'.$this->format_ts( $value );
						} else {
							$ics_file[] = strtoupper( $key ).':'.$value;
						}
					}
				}

				$ics_file[] = 'DTSTAMP:'.$this->format_ts( 'now' );
				$ics_file[] = 'UID:'.uniqid();
				$ics_file[] = 'END:VEVENT';
			}

			$ics_file[] = 'END:VCALENDAR';

			return $this->converter_to_string( $ics_file );
		}
	}

	/**
	* Convierte el Array de datos al String
	* @param  Array  $data Datos del calendario
	* @return String       Datos del calendario en String
	*/
	private function converter_to_string( array $data )
	{
		return implode("\r\n", $data);
	}

	/**
	* Formatea la fecha entrante a ISO8601
	* @param  String $timestamp Fecha en formato yyyy-mm-dd hh:i:s
	* @return String            Fecha formateada en Ymd\This
	*/
	private function format_ts($timestamp)
	{
		$date = new \DateTime( $timestamp, new \DateTimeZone( $this->timeZone ) );
		return $date->format( self::FDT );
	}

	public function save_file( $param )
	{
		$celular  			= $param[0];
		$dataContent 		= $param[1];
		$extension  		= '.ics';
		$nombre_archivo 	= 'cal'.$celular.'-'.uniqid();
		$directorio  		= '';

		$f = fopen( $directorio . $nombre_archivo . $extension, 'w' );
		fwrite( $f, $dataContent );
		fclose( $f );

		return $nombre_archivo;
	}
}

$data = [];

array_push($data, [
	'summary'   	=> 'Titulo recordatorio',
	'description' 	=> 'Texto a recordar',
	'dtstart'  		=> '2019-06-04 09:00:00',
	'dtend'   		=> '2019-06-04 10:00:00'
]);

array_push($data, [
	'summary'   	=> 'Titulo recordatorio 2',
	'description' 	=> 'Texto a recordar2',
	'dtstart'  		=> '2019-06-04 11:00:00',
	'dtend'   		=> '2019-06-04 12:00:00'
]);

$ics = new ICS('America/Panama');
$ics->set( $data );
$dataContent = $ics->build();

//Solo guarda el archivo
$ics->save_file( '6000000', $dataContent );

//Descarga el archivo sin guardar
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=recordatorio.ics');
echo $dataContent;