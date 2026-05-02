#!/bin/bash

set -e

mix deps.get

mix ecto.create 2>/dev/null || true

mix ecto.migrate

mix run -e "
  count = PhoenixApi.Repo.aggregate(PhoenixApi.Accounts.User, :count)
  if count == 0 do
    Code.eval_file(\"priv/repo/seeds.exs\")
  end
"

exec mix phx.server
