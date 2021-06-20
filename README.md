# Credits
- Plugin credits like discord probot.

# Commands
Command | Description | Aliases
--- | --- | ---
`/credits` | `To see your credits` | `/c`
`/credits <PlayerName>` | `To see the credits of a specific person` | `/c <PlayerName`
`/credits <PlayerName> <Count> <Reason>` | `Transfer credits to someone` | `/c <PlayerName> <Count> <Reason>`
`/daily` | `To get your daily reward` | `/d`

# API

- $api = Main::getInstance();
- $api->getCredits(Player); // to get player credits
- $api->addCredits(Player, $count); // to add credits
- $api->reduceCredits(Player, $count); // to reduce credits
- $api->transferCredits(Player, $toName, $count, $reason); // to transfer credits

# Other

- [![Donate](https://img.shields.io/badge/donate-Paypal-yellow.svg?style=flat-square)](https://paypal.me/Laith113)
