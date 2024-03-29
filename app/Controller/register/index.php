<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Model\Dao\User;


// 会員登録ページコントローラ
$app->get('/register/', function (Request $request, Response $response) {

    //GETされた内容を取得します。
    $data = $request->getQueryParams();

    // Render index view
    return $this->view->render($response, 'register/register.twig', $data);

});

// 会員登録処理コントローラ
$app->post('/register/', function (Request $request, Response $response) {

    //POSTされた内容を取得します
    $data = $request->getParsedBody();

    //ユーザーDAOをインスタンス化
    $user = new User($this->db);

	//全項目入力されているか
   if (!isset($data["password"]) or !isset($data["password_re"]) or !isset($data["name"]) or !isset($data["email"])) {

        //入力項目がマッチしない場合エラーを出す
        $data["error"] = "すべての項目を入力してください。";

        // 入力フォームを再度表示します
        return $this->view->render($response, 'register/register.twig', $data);

    }

    //入力されたメールアドレスの会員が登録済みかどうかをチェックします
    if ($user->select(array("email" => $data["email"]), "", "", 1, false)) {

        //入力項目がマッチしない場合エラーを出す
        $data["error"] = "このメールアドレスは既に会員登録済みです";

        // 入力フォームを再度表示します
        return $this->view->render($response, 'register/register.twig', $data);

    }

    //利用規約の同意確認
   if (!isset($data["agree"])) {

        //入力項目がマッチしない場合エラーを出す
        $data["error"] = "利用規約をお読みになり、同意される場合はチェックを入れてください。";

        // 入力フォームを再度表示します
        return $this->view->render($response, 'register/register.twig', $data);

    }

	//パスワードと再確認の一致
   if ($data["password"]!=$data["password_re"]) {

        //入力項目がマッチしない場合エラーを出す
        $data["error"] = "パスワードとパスワード再入力の内容が一致しません。";

        // 入力フォームを再度表示します
        return $this->view->render($response, 'register/register.twig', $data);
    }

    //DB登録に必要ない情報は削除します
    unset($data["password_re"]);
    unset($data["agree"]);


    //DBに登録をする。戻り値は自動発番されたIDが返ってきます
    $id = $user->insert($data);

    //今登録された情報を発番されたIDで引き、会員情報を取得します（会員登録後の自動ログイン処理のため）
    $result = $user->select(array("id" => $id), "", "", 1, false);

    //セッションにユーザー情報を登録（ログイン処理）
    $this->session->set('user_info', $result);

    // 登録完了ページを表示します。
    return $this->view->render($response, 'register/register_done.twig', $data);

});
