<?php
////==============================================////
////																				      ////
////             Контроллер D-пакета		  	      ////
////																							////
////==============================================////


/**
 *
 *
 *     HTTP-метод   Имя API     Ключ              Защита   Описание
 * ------------------------------------------------------------------------------------------------------------
 * Стандартные операции
 *
 *     GET          GET-API     любой get-запрос           Обработка всех GET-запросов
 *     POST         POST-API    любой post-запрос          Обработка всех POST-запросов
 *
 * ------------------------------------------------------------------------------------------------------------
 * Нестандартные POST-операции
 *
 *                  POST-API1   D10000:1              (v)      Описание
 *                  POST-API2   D10000:2              (v)      Описание
 *
 *
 *
 */


//-------------------------------//
// Пространство имён контроллера //
//-------------------------------//

  namespace D10000;


//---------------------------------//
// Подключение необходимых классов //
//---------------------------------//

  // Классы, поставляемые Laravel
  use Illuminate\Routing\Controller as BaseController,
      Illuminate\Support\Facades\App,
      Illuminate\Support\Facades\Artisan,
      Illuminate\Support\Facades\Auth,
      Illuminate\Support\Facades\Blade,
      Illuminate\Support\Facades\Bus,
      Illuminate\Support\Facades\Cache,
      Illuminate\Support\Facades\Config,
      Illuminate\Support\Facades\Cookie,
      Illuminate\Support\Facades\Crypt,
      Illuminate\Support\Facades\DB,
      Illuminate\Database\Eloquent\Model,
      Illuminate\Support\Facades\Event,
      Illuminate\Support\Facades\File,
      Illuminate\Support\Facades\Hash,
      Illuminate\Support\Facades\Input,
      Illuminate\Foundation\Inspiring,
      Illuminate\Support\Facades\Lang,
      Illuminate\Support\Facades\Log,
      Illuminate\Support\Facades\Mail,
      Illuminate\Support\Facades\Password,
      Illuminate\Support\Facades\Queue,
      Illuminate\Support\Facades\Redirect,
      Illuminate\Support\Facades\Redis,
      Illuminate\Support\Facades\Request,
      Illuminate\Support\Facades\Response,
      Illuminate\Support\Facades\Route,
      Illuminate\Support\Facades\Schema,
      Illuminate\Support\Facades\Session,
      Illuminate\Support\Facades\Storage,
      Illuminate\Support\Facades\URL,
      Illuminate\Support\Facades\Validator,
      Illuminate\Support\Facades\View;

  // Модели и прочие классы



//------------//
// Контроллер //
//------------//
class Controller extends BaseController {

  //-------------------------------------------------//
  // ID пакета, которому принадлежит этот контроллер //
  //-------------------------------------------------//
  public $packid = "D10000";
  public $layoutid = "L10000";

  //--------------------------------------//
  // GET-API. Обработка всех GET-запросов //
  //--------------------------------------//
  public function getIndex() {

    //----------------------------------------------------------------------------------//
    // Провести авторизацию прав доступа запрашивающего пользователя к этому интерфейсу //
    //----------------------------------------------------------------------------------//
    // - Если команда для проведения авторизации доступна, и если авторизация включена.
    if(class_exists('\M5\Commands\C66_authorize_access') && config("M5.authorize_access_ison") == true) {

      // Провести авторизацию
      $authorize_results = runcommand('\M5\Commands\C66_authorize_access', ['packid' => $this->packid, 'userid' => lib_current_user_id()]);

      // Если доступ запрещён, вернуть документ с кодом 403
      if($authorize_results['status'] == -1)
        return Response::make("Unfortunately, access to this document is forbidden for you.", 403);

    }

    //-----------------------//
    // Обработать GET-запрос //
    //-----------------------//

      // 1. Получить items_at_page первых групп для 1-й страницы
      $groups = (array) runcommand('\M5\Commands\C6_groups', [
        "page"            => 1,
        "pages_total"     => "",
        "items_at_page"   => config('M5.items_at_page'),
        "filters"         => json_encode("", JSON_UNESCAPED_UNICODE)
      ]);

      // 2. Получить items_at_page первых прав для 1-й страницы
      $privs = runcommand('\M5\Commands\C7_privileges', [
        "page"            => 1,
        "pages_total"     => "",
        "items_at_page"   => config('M5.items_at_page'),
        "filters"         => json_encode("", JSON_UNESCAPED_UNICODE)
      ]);

      // 3. Получить items_at_page первых тегов для 1-й страницы
      $tags = runcommand('\M5\Commands\C8_tags', [
        "page"            => 1,
        "pages_total"     => "",
        "items_at_page"   => config('M5.items_at_page'),
        "filters"         => json_encode("", JSON_UNESCAPED_UNICODE)
      ]);

      // 4. Получить items_at_page первых пользователей для 1-й страницы
      $users = runcommand('\M5\Commands\C5_users', [
        "page"            => 1,
        "pages_total"     => "",
        "items_at_page"   => config('M5.items_at_page'),
        "filters"         => json_encode("", JSON_UNESCAPED_UNICODE)
      ]);

      // 5. Сформировать стоку с websocket_server
      $websocket_server = call_user_func(function(){

        // 5.1. Получить протокол запроса
        $protocol = \Request::secure() ? "https://" : "http://";

        // 5.2. Получить хост запроса
        $host = \Request::getHost();

        // 5.3. Сформировать результат
        return $protocol . $host . ':6001';

      });

      // M. Извлечь из GET-запроса параметр page
      if(Request::has('page')) $page = Request::input('page');
      else $page = '';

      // N. Вернуть клиенту представление и данные $data
      return View::make($this->packid.'::view', ['data' => json_encode([

        'document_locale'       => r1_get_doc_locale($this->packid),
        'auth'                  => session('auth_cache') ?: '',
        'page'                  => $page,
        'packid'                => $this->packid,
        'layoutid'              => $this->layoutid,
        'groups'                => $groups,
        'privs'                 => $privs,
        'tags'                  => $tags,
        'users'                 => $users,
        'websocket_server'      => $websocket_server,
        'websockets_channel'    => Session::getId()

      ]), 'layoutid' => $this->layoutid.'::layout']);



  } // конец getIndex()


  //----------------------------------------//
  // POST-API. Обработка всех POST-запросов //
  //----------------------------------------//
  public function postIndex() {

    Log::info(111);

    //----------------------------------------------------------------------------------//
    // Провести авторизацию прав доступа запрашивающего пользователя к этому интерфейсу //
    //----------------------------------------------------------------------------------//
    // - Если команда для проведения авторизации доступна, и если авторизация включена.
    if(class_exists('\M5\Commands\C66_authorize_access') && config("M5.authorize_access_ison") == true) {

      // Провести авторизацию
      $authorize_results = runcommand('\M5\Commands\C66_authorize_access', ['packid' => $this->packid, 'userid' => lib_current_user_id()]);

      // Если доступ запрещён, вернуть документ с кодом 403
      if($authorize_results['status'] == -1)
        return Response::make("Unfortunately, access to this document is forbidden for you.", 403);

    }

    Log::info(222);

    //------------------------//
    // Обработать POST-запрос //
    //------------------------//

      //------------------------------------------//
      // 1] Получить значение опций key и command //
      //------------------------------------------//
      // - $key       - ключ операции (напр.: D10000:1)
      // - $command   - полный путь команды, которую требуется выполнить
      $key        = Input::get('key');
      $command    = Input::get('command');


      //----------------------------------------//
      // 2] Обработка стандартных POST-запросов //
      //----------------------------------------//
      // - Это около 99% всех POST-запросов.
      if(empty($key) && !empty($command)) {

        // 1. Получить присланные данные

          // Получить данные data
          $data = Input::get('data');   // массив


        // 2. Выполнить команду и получить результаты
        $response = runcommand(

            $command,                   // Какую команду выполнить
            $data,                      // Какие данные передать команде
            lib_current_user_id()       // ID пользователя, от чьего имени выполнить команду

        );


        // 3. Добавить к $results значение timestamp поступления запроса
        $response['timestamp'] = $data['timestamp'];


        // 4. Сформировать ответ и вернуть клиенту
        return Response::make(json_encode($response, JSON_UNESCAPED_UNICODE));

      }


      //------------------------------------------//
      // 3] Обработка нестандартных POST-запросов //
      //------------------------------------------//
      // - Очень редко алгоритм из 2] не подходит.
      // - Например, если надо принять файл.
      // - Тогда $command надо оставить пустой.
      // - А в $key прислать ключ-код номер операции.
      if(!empty($key) && empty($command)) {

        //-----------------------------//
        // Нестандартная операция D10000:1 //
        //-----------------------------//
        if($key == 'D10000:1') {



        }


      }

  } // конец postIndex()


}?>