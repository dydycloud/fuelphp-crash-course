<?php
class Controller_Messages extends Controller_Template 
{

	public function action_index()
	{
		$view = View::factory('messages/index');
		$messages = Model_Message::find('all');
		$comments = array();
		foreach($messages as $message)
		{
			$query = Model_Comment::find()->where("mid", $message['id']);
			$comments[$message['id']] = $query->count();
			if($comments[$message['id']] == 0)
			{
				$comments[$message['id']] = "View";
			}
			else
			{
				$comments[$message['id']].=" Comment";
				if($comments[$message['id']] > 1)
				{
					$comments[$message['id']].="s";
				}
			}
		}
		$view->set('comments', $comments);
		$view->set('messages', $messages);
		$this->template->title = "Messages";
		$this->template->content = $view;
	}

	public function action_view($id = null)
	{
		$data['message'] = Model_Message::find($id);
		$comments= Model_Comment::find()->where('mid', $id)->get();
		$data['comments'] = $comments;
		$this->template->title = "Message";
		$this->template->content = View::forge('messages/view', $data);

	}

	public function action_create($id = null)
	{
		if (Input::method() == 'POST')
		{
			$val = Model_Message::validate('create');
			
			if ($val->run())
			{
				$message = Model_Message::forge(array(
					'name' => Auth::instance()->get_screen_name(),
					'message' => Input::post('message'),
				));

				if ($message and $message->save())
				{
					Session::set_flash('success', 'Added message #'.$message->id.'.');

					Response::redirect('messages');
				}

				else
				{
					Session::set_flash('error', 'Could not save message.');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
		}

		$this->template->title = "Messages";
		$this->template->content = View::forge('messages/create');

	}

	public function action_edit($id = null)
	{
		$message = Model_Message::find($id);
		$val = Model_Message::validate('edit');

		if ($val->run())
		{
			$message->name = Input::post('name');
			$message->message = Input::post('message');

			if ($message->save())
			{
				Session::set_flash('success', 'Updated message #' . $id);

				Response::redirect('messages');
			}

			else
			{
				Session::set_flash('error', 'Could not update message #' . $id);
			}
		}

		else
		{
			if (Input::method() == 'POST')
			{
				$message->name = $val->validated('name');
				$message->message = $val->validated('message');

				Session::set_flash('error', $val->show_errors());
			}
			
			$this->template->set_global('message', $message, false);
		}

		$this->template->title = "Messages";
		$this->template->content = View::forge('messages/edit');

	}

	public function action_delete($id = null)
	{
		if ($message = Model_Message::find($id))
		{
			$message->delete();

			Session::set_flash('success', 'Deleted message #'.$id);
		}

		else
		{
			Session::set_flash('error', 'Could not delete message #'.$id);
		}

		Response::redirect('messages');

	}


}