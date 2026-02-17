# Installation
Run `make build` in order to install all application dependencies (you must have Docker installed).

For more commands, type `make help`

# About how this project is structured
There are some documents explaining the decisions made in this project, and the reasons behind them.
You can find them in the `docs/technicalDecisions` folder.

# Useful tools included in the project
- `RedisInsight`: Small web application to manage and monitor Redis instances. You can access it at http://localhost:5540/
   - Click on `+ Connect existing database` and set the *Connection URL* as `redis://default@redis_container:6379`
-  
