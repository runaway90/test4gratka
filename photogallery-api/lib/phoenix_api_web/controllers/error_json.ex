defmodule PhoenixApiWeb.ErrorJSON do
  def render(_template, %{message: message}) do
    %{errors: %{detail: message}}
  end

  def render(template, _assigns) do
    %{errors: %{detail: Phoenix.Controller.status_message_from_template(template)}}
  end
end
