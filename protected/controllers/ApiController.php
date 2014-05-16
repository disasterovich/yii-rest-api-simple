<?php

class ApiController extends Controller
    {
    public function filters()
        {
        return array();
        }

    //Нужно пользователю дать возможность просмотреть расписание кинотеатра, с возможностью фильтрации по залу:
    //GET /api/cinema/<название кинотеатра>/schedule[?hall=номер зала]
    public function actionCinemaSchedule($cinema_name="",$hall=0)
        {
        if ($cinema_name == "") {$this->_sendResponse(500, 'Не задано название кинотеатра' );}
        
        $criteria=new CDbCriteria;
        $criteria->select = 't.id,t.name';
        
        $criteria->with=array();
        
        $criteria->with['halls'] = array( 'select'=>'halls.name', );
        
        //Фильтр по залу
        if ( $hall )
            {
            $criteria->with['halls']['condition'] = 'halls.id=:hall_id';
            $criteria->with['halls']['params'] = array( ':hall_id'=>$hall );
            }

        $criteria->with['halls.sessions'] = array( 'select'=>'sessions.start_time, sessions.end_time',  ); 
        
        $criteria->with['halls.sessions.films'] = array( 'select'=>'films.name',  );  

        $criteria->condition = 't.name=:name';
        $criteria->params = array( ':name'=>$cinema_name, );
        
        $cinema = Cinemas::model()->findAll( $criteria );

        if ($cinema === null)        
            {
            $this->_sendResponse(400, "Ничего не найдено");
            }
        else
            {
            $result = array();
            
            foreach( $cinema as $c )
                {
                //Получаем список залов
                foreach ($c->halls as $c2)
                    {
                    foreach ($c2->sessions as $c3)
                        {
                        $result[] = array
                            ( 
                            'film_name'=>$c3->films->name,
                            'start'=>date('H:i',$c3->start_time),
                            'end'=>date('H:i',$c3->end_time),
                            'hall'=>$c2->name
                            );
                        }
                    }
                }
            
            $this->_sendResponse(200, CJSON::encode($result));
            }
        }
        
    //Также надо дать возможность просмотреть в каких кинотеатрах/залах идёт конкретный фильм:
    //GET /api/film/<название фильма>/schedule
    public function actionFilm($film_name="")
        {
        if ($film_name == "") {$this->_sendResponse(500, 'Не задано название фильма' );}
        
        $criteria=new CDbCriteria;
        $criteria->select = 't.id,t.name';
        
        $criteria->with=array();
        
        $criteria->with['sessions'] = array( 'select'=>'sessions.start_time, sessions.end_time',  ); 
        
        $criteria->with['sessions.halls'] = array( 'select'=>'halls.name', );
        
        $criteria->with['sessions.halls.cinemas'] = array( 'select'=>'cinemas.name', );

        $criteria->condition = 't.name=:name';
        $criteria->params = array( ':name'=>$film_name, );
        
        $film = Films::model()->findAll( $criteria );        
        
        if ($film === null)        
            {
            $this->_sendResponse(400, "Ничего не найдено");
            }
        else
            {
            $result = array();
            
            foreach( $film as $c )
                {
                foreach ($c->sessions as $c2)
                    {
                    $result[] = array
                        (
                        'cinema_name'=>$c2->halls->cinemas->name,
                        'start'=>date('H:i',$c2->start_time),
                        'end'=>date('H:i',$c2->end_time),
                        'hall'=>$c2->halls->name
                        );
                    }
                }
            
            $this->_sendResponse(200, CJSON::encode($result));
            }

        }
        
    //И дать возможность купить билет:
    //POST /api/tickets/buy?session=<id сеанса>&places=1,3,5,7
    //Результатом запроса должен быть уникальный код, характеризующий этот набор билетов
    public function actionBuyTicket($session=0, $places="")
        {
        if ($session == 0) {$this->_sendResponse(500, 'Не задан id сеанса' );}
        if ($places == "") {$this->_sendResponse(500, 'Не заданы места' );}
        
        $places_arr = explode(",",$places);

        $criteria=new CDbCriteria;

        $criteria->with=array();

        $criteria->with['orders'] = array( 'condition'=>'session_id=:session_id', 'params'=>array(':session_id'=>$session) ); 

        $criteria->condition = 't.seat_number IN (:seat_number)';
        $criteria->params = array( ':seat_number'=>$places, );

        if ( !OrdersPlaces::model()->exists( $criteria ) )
            {
            //Делаем покупку
            $order = new Orders("insert");
            $order_places = new OrdersPlaces("insert");
            
            $order->session_id = $session;
            
            //Возможно нужно использ. транзанкций?
            if ( $order->save() )
                {
                foreach ($places_arr as $v)
                    {
                    //Бронируем места
                    $order_places->isNewRecord = true;
                    $order_places->id = NULL;

                    $order_places->order_id = $order->id;
                    $order_places->seat_number = (int)$v;
                    
                    $order_places->save();
                    }

                $this->_sendResponse(200, $order->code);
                }
            else 
                {
                $this->_sendResponse(500, "Ошибка сохранения заявки");
                }
            }
        else
            {
            $this->_sendResponse(400, 'Одно или несколько из выбранных мест уже куплено.');
            }
        }
        
    //И отменить покупку, но не раньше, чем за час до начала сеанса:
    //POST /api/tickets/reject/<уникальный код>
    public function actionRejectTicket($code="")
        {
        if ($code == "") {$this->_sendResponse(500, 'Не задан код покупки' );}
        
        $order = Orders::model()->find('code=:code',array(':code'=>$code));
        
        if ($order === null)        
            {
            $this->_sendResponse(400, "Заказ с данным кодом не найден");
            }
        else
            {
            $session = Sessions::model()->find('id=:session_id',array(':session_id'=>$order->session_id));
            
            if ( $session->start_time - time() <= 3600 ) {$this->_sendResponse(400, 'Вы не можете отменить заказ билетов менее чем за час до начала сеанса' );}
            
            $del_op = OrdersPlaces::model()->deleteAll('order_id=:order_id',array(':order_id'=>$order->id));
            
            if ( $order->delete()>0 AND $del_op >0 )
                {
                $this->_sendResponse(200, "Заказ билетов отменен");
                }
            else 
                {
                $this->_sendResponse(500, "Ошибка удаления заявки");
                }
            }
        }
        
    //Затем надо проверить, какие места свободны на конкретный сеанс:
    //GET /api/session/<id сеанса>/places
    public function actionShowSeats( $session_id="" )
        {
        if ($session_id == "") {$this->_sendResponse(500, 'Не задан id сеанса' );}
        
        $session = Sessions::model()->findByPk($session_id);
        
        if ($session === null)        
            {
            $this->_sendResponse(400, "Сеанс не найден");
            }
        
        //Получим кол-во мест для зала
        $hall = Halls::model()->findByPk($session->hall_id);
        
        $criteria=new CDbCriteria;
        $criteria->with=array();
        $criteria->with['orders'] = array( 'condition'=>'session_id=:session_id', 'params'=>array(':session_id'=>$session_id) ); 
        
        $order_places = OrdersPlaces::model()->findAll( $criteria );
        
        $all_places = array();
        $busy_places = array();
        
        foreach ($order_places as $o_p) { $busy_places[] = $o_p->seat_number; }
        
        for ( $i=1; $i<=$hall->seating_capacity; $i++ ) { $all_places[] = $i; }
        
        $free_places = array_diff($all_places, $busy_places);
        
        $this->_sendResponse(200, CJSON::encode($free_places));
        }

    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
        {
        $status_header = 'HTTP/1.1 '.$status.' '.$this->_getStatusCodeMessage($status);
        header($status_header);
        header('Content-type: '.$content_type);

        //if($body != '') { echo $body; }
        echo $body;

        Yii::app()->end();
        }
        
    private function _getStatusCodeMessage($status)
        {
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
        }
        
    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
        {
        if($error=Yii::app()->errorHandler->error)
            {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
            }
        }
        
    }

?>