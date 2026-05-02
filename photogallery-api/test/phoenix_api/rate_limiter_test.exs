defmodule PhoenixApi.RateLimiterTest do
  use ExUnit.Case, async: false

  alias PhoenixApi.RateLimiter

  setup do
    if pid = Process.whereis(RateLimiter) do
      GenServer.stop(pid)
    end

    {:ok, _pid} = RateLimiter.start_link([])
    :ok
  end

  describe "per-user limit" do
    test "allows up to 5 requests from the same user" do
      for _ <- 1..5 do
        assert :ok = RateLimiter.check_and_increment(:user_a)
      end
    end

    test "blocks the 6th request from the same user" do
      for _ <- 1..5 do
        RateLimiter.check_and_increment(:user_b)
      end

      assert {:error, :user_limit_exceeded} = RateLimiter.check_and_increment(:user_b)
    end

    test "different users have independent limits" do
      for _ <- 1..5 do
        RateLimiter.check_and_increment(:user_c)
      end

      assert :ok = RateLimiter.check_and_increment(:user_d)
    end
  end

  describe "global limit" do
    test "counts requests from all users toward global limit" do
      for i <- 1..10 do
        assert :ok = RateLimiter.check_and_increment(:"global_user_#{i}")
      end
    end

    test "blocks requests when global limit is exceeded" do
      for i <- 1..1000 do
        RateLimiter.check_and_increment(:"bulk_user_#{i}")
      end

      assert {:error, :global_limit_exceeded} = RateLimiter.check_and_increment(:new_user)
    end
  end
end
