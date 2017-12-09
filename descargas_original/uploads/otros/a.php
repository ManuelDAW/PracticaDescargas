echo "<h2>MRM ---DEBUGANDO ...en create_matrices_fast </h2>";
        //Inicializo el primer nivel del array
        $matrix[ 'host' ] = array_values( $datas )[ 0 ]->host_id;
        $matrix[ 'date' ] = []; //Será un array indexado de las diferentes fechas
        $matrix[ 'services' ] = []; //Array asociativo con cada servicio cada uno tendrá un arrau con los índices
        //  //Ahora recorremos para cada documento  (host - día)
        //primero inicializamos las fechas
        $min = "00";

        //Nos quedamos con los dos array si los ubiera si no data2 tendrá un false
        reset( $datas );
        $data1 = current( $datas );
        $data2 = next( $datas );

        $fecha = substr( $data1->timestamp, 0, 10 ) . " $hour:00:00";
        $dateMAX = date( 'Y-m-d H:i:00', strtotime( $fecha . " -3 hour" ) );
        $fecha = date( 'Y-m-d H:i:00', strtotime( $fecha . " -2 minute" ) );
        $matrix[ 'date' ] = Charts_Controller::get_array_datetime_range( $dateMAX, $fecha, 60, 'H:i:00' );

        $services = array_keys( $data1->data );

        foreach ( $list_services as $service ) {
            $matrix[ 'services' ][ $service ] = [];
            $indexes_service = array_keys( $data1->data[ $service ] );


            //Para cada índice de ese servicio
            foreach ( $indexes_service as $index_service ) {
                //Si hora =1 o 2 necesito datos de los dos días para generar la matriz
                //Si no lo cogeré de cada día
                $datas_of_mongo = [];
                switch ( $hour ) {
                    case 1:
                        $h = 22;
                        //Primer array de documentos
                        $datas_of_mongo = $data1->data[ $service ][ $index_service ][ $h ];
                        $datas_of_mongo = array_merge( $datas_of_mongo, $data1->data[ $service ][ $index_service ][ $h + 1 ] );
                        $datas_of_mongo = array_merge( $datas_of_mongo, $data2->data[ $service ][ $index_service ][ 0 ] );
                        break;

                    case 2:
                        $h = 23;
                        //Primer array de documentos
                        $datas_of_mongo = $data1->data[ $service ][ $index_service ][ $h ];
                        $datas_of_mongo = array_merge( $datas_of_mongo, $data2->data[ $service ][ $index_service ][ 0 ] );
                        $datas_of_mongo = array_merge( $datas_of_mongo, $data2->data[ $service ][ $index_service ][ 1 ] );
                        break;
                    default:

                        //Primer array de documentos
                        $datas_of_mongo = $data1->data[ $service ][ $index_service ][ $h ];
                        $data_of_mongo = array_merge( $datas_of_mongo, $data1->data[ $service ][ $index_service ][ ++$h ] );
                        $data_of_mongo = array_merge( $datas_of_mongo, $data1->data[ $service ][ $index_service ][ ++$h ] );
                }
                $matrix[ 'services' ][ $service ][ $index_service ] = $datas_of_mongo;
            }//End while index (indices de los servicios
            //                echo "FECHA       -      VALOR SERVICIO<br/>";
        }//End foreach $services
        //ver matriz
        Logs_Controller::vardump( $matrix );

        return ($matrix);

