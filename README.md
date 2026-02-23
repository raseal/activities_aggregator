# ACTIVITIES AGGREGATOR
## What is this project about?
This project is a simple application that aggregates activities from different sources and provides an API to access them.
The main focus of the app is scalability and maintainability, so it is designed to be easily extendable and to handle a large amount of data.

## The problem: what are we trying to solve?
We have different sources of activities (e.g. musical events, stand-ups comedy, etc.) and we want to aggregate them in a single place and provide an API to access them.

_ℹ️ For this scenario, we will work only with one single provider, but the app could handle multiple data sources_

Let's assume that we have a provider that exposes a public API (a very large XML file) with all the activities. The XML would look like this:

```xml
<output>
    <base_event base_event_id="291" sell_mode="online" title="The lion king">
        <event event_start_date="2021-06-30T21:00:00" event_end_date="2021-06-30T22:00:00" event_id="291" sell_from="2020-07-01T00:00:00" sell_to="2021-06-30T20:00:00" sold_out="false">
            <zone zone_id="40" capacity="243" price="20.00" name="Balcony" numbered="true" />
            <zone zone_id="38" capacity="100" price="15.00" name="Stage" numbered="false" />
            <zone zone_id="30" capacity="90" price="30.00" name="A28" numbered="true" />
        </event>
    </base_event>
    <base_event base_event_id="322" sell_mode="online" organizer_company_id="2" title="Cats">
        <event event_start_date="2021-02-10T20:00:00" event_end_date="2021-02-10T21:30:00" event_id="1642" sell_from="2021-01-01T00:00:00" sell_to="2021-02-09T19:50:00" sold_out="false">
            <zone zone_id="311" capacity="2" price="55.00" name="A42" numbered="true" />
        </event>
    </base_event>
    <base_event base_event_id="1591" sell_mode="online" organizer_company_id="1" title="The phantom of the opera">
        <event event_start_date="2021-07-31T20:00:00" event_end_date="2021-07-31T21:00:00" event_id="1642" sell_from="2021-06-26T00:00:00" sell_to="2021-07-31T19:50:00" sold_out="false">
            <zone zone_id="186" capacity="2" price="75.00" name="Balcony" numbered="true" />
            <zone zone_id="186" capacity="16" price="65.00" name="Stage" numbered="false" />
        </event>
    </base_event>
</output>
```
The XML can, potentially, have thousands of events, and each event can have hundreds of zones. 

Besides that, the provider updates the XML file every X hours, so we need to be able to handle updates and new events.

## How do we solve it?
We split the app into two `Bounded Contexts`: 
- Ingestor
- Catalog

The first one is responsible for fetching the XML file, parsing it and storing the data in a database. It also publishes `Domain events` to notify the Catalog about new events or updates.

The second one is responsible for providing an API to access the processed activities. It listens to the `Domain events` published by the Ingestor and updates its own projections accordingly.

Since readings will be much more frequent than writings, we decided to use a `CQRS` architecture, so the Catalog has its own independent database for reads and writes.

### About how this project is structured
There are some documents explaining the decisions made in this project, and the reasons behind them.
You can find them in the `docs/technicalDecisions` folder.

## Installation
Run `make build` in order to install all application dependencies (you must have Docker installed).

For more commands, type `make help`

## Useful tools included in the project
- `RedisInsight`: Small web application to manage and monitor Redis instances. You can access it at http://localhost:5540/
   - Click on `+ Connect existing database` and set the *Connection URL* as `redis://default@redis_container:6379`
-  `RabbitMQ Management`: Web application to manage and monitor RabbitMQ instances. You can access it at http://localhost:15672/
   - Login with username `admin` and password `admin`

# TODO
- Update docs with `outbox pattern`
- Logging :loadog_gif:
- Second context :loading_gif:
