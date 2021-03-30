
<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Bbs extends CI_Controller
{
    public function __construct()
    {
        // CI_Model constructor の呼び出し
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Bbs_model');
        date_default_timezone_set('Asia/Tokyo');
    }

    public function index()
    {
        $data = null;
        $data['message_array'] = $this->Bbs_model->fetch_all_rows();
        
        if (!empty($_SESSION['success_message'])) {
            $data['success_message'] = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        }
        if (!empty($_SESSION['error_message'])) {
            $data['error_message'] = $_SESSION['error_message'];
            unset($_SESSION['error_message']);
        }
        $this->load->view('header_view');
        $this->load->view('bbs_view', $data);
    }

    public function add_bbs()
    {
        $name = @$this->input->post('view_name', true) ?: null;
        $message = @$this->input->post('message', true) ?: null;
        $error_message = null;
        if (empty($name)) {
            $error_message[] = '表示名を入力してください。';
        }
        if (empty($message)) {
            $error_message[] = 'ひと言メッセージを入力してください';
        }
        if (empty($error_message)) {
            $data = [
                'view_name' => $name,
                'message' => $message,
                'post_date' => date("Y-m-d H:i:s")
            ];
            if ($this->Bbs_model->insert_row($data)) {
                $_SESSION['success_message'] = 'メッセージを書き込みました。';
            } else {
                $_SESSION['error_message'] = '登録に失敗しました。';
            }
        } else {
            $_SESSION['error_message'] = $error_message;
        }
        header('location: /CodeIgniterkeiziban/bbs');
        exit();
    }


    // ログイン・編集コーナー
    public function login()
    {
        if (!empty($_SESSION['admin_login'])) {
            header('location: /admin');
            exit();
        }
        $this->load->view('header_view');
        $this->load->view('login_view');
    }

    public function logout()
    {
        if (!empty($_SESSION['admin_login'])) {
            session_destroy();
        }
        header('location: /CodeIgniterkeiziban/bbs/login');
        exit();
    }

        /**
     * 管理者ログインチェック
     */
    public function attempt_login()
    {
        header("Content-Type: application/json; charset=utf-8");
        //JSONのデータ形式で値を受け取る
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //adminPasswordという文字列を暗号化したものが入っています
            define('PASSWORD', '$2y$10$SbdHurka6tRt02PSRxfNMOOFUnCSSPnnFmq8RWjoTIpTTfLTKdCr6');
            if (empty($this->input->post('admin_password', true))) {
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode(['message' => 'パスワードが間違っています']);
                exit();
            }
            $password = $this->input->post('admin_password', true);
            if (!password_verify($password, PASSWORD)) {
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode(['message' => 'パスワードが間違っています']);
                exit();
            }
            $_SESSION['admin_login'] = true;
            echo json_encode(['message' => '認証成功']);
        } else {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['message' => '許可されていないメソッドです']);
        }
        exit();
    }

    public function admin()
    {
        if (!empty($_SESSION['admin_login'])) {
            $data = null;
            if (!empty($_SESSION['success_message'])) {
                $data['success_message'] = $_SESSION['success_message'];
                unset($_SESSION['success_message']);
            }
            if (!empty($_SESSION['error_message'])) {
                $data['error_message'] = $_SESSION['error_message'];
                unset($_SESSION['error_message']);
            }
            $data['message_array'] = $this->Bbs_model->fetch_all_rows();
            $this->load->view('header_view');
            $this->load->view('admin_view', $data);
        } else {
            header('location: /CodeIgniterkeiziban/bbs/login');
            exit();
        }
    }


    public function edit()
    {
        if (!empty($_SESSION['admin_login'])) {
            $id = @$this->input->get('message_id') ?: null;
            if (!empty($id) && is_numeric($id)) {
                $data = null;
                if (!empty($_SESSION['error_message'])) {
                    $data['error_message'] = $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                }
                $data['message_data'] = $this->Bbs_model->fetch_one_row($id);
                if (!empty($data['message_data'])) {
                    $this->load->view('header_view');
                    $this->load->view('admin_edit_view', $data);
                } else {
                    $_SESSION['error_message'][] = '存在しないレコードです。';
                    header('location: /bbs/admin');
                    exit();
                }
            } else {
                $_SESSION['error_message'][] = '更新に必要なパラメータが含まれていません';
                header('location: /bbs/admin');
                exit();
            }
        } else {
            header('location: /CodeIgniterkeiziban/bbs/login');
            exit();
        }
    }

    public function delete()
    {   
        if (!empty($_SESSION['admin_login'])) {
            $id = @$this->input->get('message_id') ?: null;
            if (!empty($id) && is_numeric($id)) {
                $data = null;
                if (!empty($_SESSION['error_message'])) {
                    $data['error_message'] = $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                }
                $data['message_data'] = $this->Bbs_model->fetch_one_row($id);
                if (!empty($data['message_data'])) {
                    $this->load->view('header_view');
                    $this->load->view('admin_delete_view', $data);
                } else {
                    $_SESSION['error_message'][] = '存在しないレコードです。';
                    header('location: http://localhost/CodeIgniterkeiziban/bbs/admin');
                    exit();
                }
            } else {
                $_SESSION['error_message'][] = '更新に必要なパラメータが含まれていません';
                header('location: /bbs/admin');
                exit();
            }
        } else {
            header('location: http://localhost/CodeIgniterkeiziban/bbs/login');
            exit();
        }
    }


    
    /**
     * POSTされてきたidでレコードを削除する
     *
     * @return void
     */
    public function delete_bbs()
    {
        if (!empty($id = $this->input->post('message_id', true))) {
            $id = $this->input->post('message_id', true);
            if ($this->Bbs_model->delete_row($id)) {
                $_SESSION['success_message'] = 'メッセージを削除しました。';
                header('location: http://localhost/CodeIgniterkeiziban/bbs/admin');
                exit();
            } else {
                $_SESSION['error_message'][] = '削除に失敗しました。';
                header("location: http://localhost/CodeIgniterkeiziban/bbs/delete?message_id={$id}");
                exit();
            }
        } else {
            $_SESSION['error_message'][] = '削除に必要なパラメータが含まれていません';
            header('location: http://localhost/CodeIgniterkeiziban/bbs/admin');
            exit();
        }
    }

        /**
     * POSTされてきたデータでレコードを更新する
     *
     * @return void
     */
    public function update_bbs()
    {
        if (!empty($this->input->post('message_id', true))) {
            $id = $this->input->post('message_id', true);
            $name = @$this->input->post('view_name', true) ?: null;
            $message = @$this->input->post('message', true) ?: null;
            $error_message = null;
            if (empty($name)) {
                $error_message[] = '表示名を入力してください。';
            }

            if (empty($message)) {
                $error_message[] = 'ひと言メッセージを入力してください';
            }

            if (empty($error_message)) {
                $data = [
                    'view_name' => $name,
                    'message' => $message,
                    'post_date' => date("Y-m-d H:i:s")
                ];
                if ($this->Bbs_model->update_row($id, $data)) {
                    $_SESSION['success_message'] = 'メッセージを更新しました。';
                    header('location: http://localhost/CodeIgniterkeiziban/bbs/admin');
                } else {
                    $_SESSION['error_message'][] = '更新に失敗しました。';
                    header("location: http://localhost/CodeIgniterkeiziban/bbs/edit?message_id={$id}");
                }
            } else {
                $_SESSION['error_message'] = $error_message;
                header("location: http://localhost/CodeIgniterkeiziban/bbs/edit?message_id={$id}");
            }
        } else {
            $_SESSION['error_message'][] = '更新に必要なパラメータが含まれていません';
            header('location: http://localhost/CodeIgniterkeiziban/bbs/admin');
        }
        exit();
    }

    public function download_csv()
    {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=メッセージデータ.csv");
        header("Content-Transfer-Encoding: binary");
        $limit = null;
        if (!empty($this->input->get('limit')) && is_numeric($this->input->get('limit'))) {
            $limit = $this->input->get('limit');
        }
        $message_array = $this->Bbs_model->fetch_all_rows($limit);
        if (!empty($message_array)) {
            $csv_data = null;
            $csv_data .= '"ID","表示名","メッセージ","投稿日時"' . "\n";
            foreach ($message_array as $value) {
                $csv_data .= '"' . $value['id'] . '","' . $value['view_name'] . '","' . $value['message'] . '","' . $value['post_date'] . "\"\n";
            }
            $csv_data = mb_convert_encoding($csv_data, "SJIS", "UTF-8");
            echo $csv_data;
        } else {
            $_SESSION['error_message'][] = 'csvの出力に失敗しました。';
            header('location: /CodeIgniterkeiziban/bbs/admin');
        }
        exit();
    }




}