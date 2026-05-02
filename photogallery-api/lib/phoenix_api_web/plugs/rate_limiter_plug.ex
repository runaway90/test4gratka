defmodule PhoenixApiWeb.Plugs.RateLimiterPlug do
  import Plug.Conn
  import Phoenix.Controller

  alias PhoenixApi.RateLimiter

  def init(opts), do: opts

  def call(conn, _opts) do
    user = conn.assigns[:current_user]

    case RateLimiter.check_and_increment(user.id) do
      :ok ->
        conn

      {:error, :user_limit_exceeded} ->
        conn
        |> put_resp_header("retry-after", "600")
        |> put_status(:too_many_requests)
        |> put_view(json: PhoenixApiWeb.ErrorJSON)
        |> render(:"429", message: "Rate limit exceeded: maximum 5 imports per 10 minutes per user")
        |> halt()

      {:error, :global_limit_exceeded} ->
        conn
        |> put_resp_header("retry-after", "3600")
        |> put_status(:too_many_requests)
        |> put_view(json: PhoenixApiWeb.ErrorJSON)
        |> render(:"429", message: "Rate limit exceeded: maximum 1000 imports per hour")
        |> halt()
    end
  end
end
