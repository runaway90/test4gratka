defmodule PhoenixApi.RateLimiter do
  use GenServer

  @user_limit 5
  @user_window_ms 10 * 60 * 1000
  @global_limit 1000
  @global_window_ms 60 * 60 * 1000

  # Public API

  def start_link(_opts) do
    GenServer.start_link(__MODULE__, nil, name: __MODULE__)
  end

  @doc """
  Checks if the request is allowed and increments counters.
  Returns :ok or {:error, :user_limit_exceeded | :global_limit_exceeded}.
  """
  def check_and_increment(user_id) do
    GenServer.call(__MODULE__, {:check_and_increment, user_id})
  end

  # GenServer callbacks

  @impl true
  def init(_) do
    state = %{
      # %{user_id => [monotonic_timestamp, ...]}
      user_requests: %{},
      global_count: 0,
      global_window_start: now()
    }

    {:ok, state}
  end

  @impl true
  def handle_call({:check_and_increment, user_id}, _from, state) do
    current_time = now()

    {global_count, global_window_start} = reset_global_if_expired(state, current_time)

    if global_count >= @global_limit do
      {:reply, {:error, :global_limit_exceeded}, %{state | global_count: global_count, global_window_start: global_window_start}}
    else
      recent = recent_user_requests(state.user_requests, user_id, current_time)

      if length(recent) >= @user_limit do
        {:reply, {:error, :user_limit_exceeded}, %{state | global_count: global_count, global_window_start: global_window_start}}
      else
        new_state = %{
          user_requests: Map.put(state.user_requests, user_id, [current_time | recent]),
          global_count: global_count + 1,
          global_window_start: global_window_start
        }

        {:reply, :ok, new_state}
      end
    end
  end

  # Private helpers

  defp now, do: System.monotonic_time(:millisecond)

  defp reset_global_if_expired(%{global_count: count, global_window_start: window_start}, current_time) do
    if current_time - window_start >= @global_window_ms do
      {0, current_time}
    else
      {count, window_start}
    end
  end

  defp recent_user_requests(user_requests, user_id, current_time) do
    user_requests
    |> Map.get(user_id, [])
    |> Enum.filter(fn ts -> current_time - ts < @user_window_ms end)
  end
end
