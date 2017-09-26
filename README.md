# Cheat Online
An online card game for friends and AI alike.

### Overview
This is an online application for the card game _Cheat_ built with PHP and MySQL. The reason I built this was my friend lost his deck of cards with which our friend group would play cards with at lunchtime, including _Cheat_, so I decided to make bring it online. No more pesky cards to worry about!

There are no modes: single-player and multi-player. `cheat_game.php` is the main script for the single-player mode, and `cheat_mp.php` is the main script for the multi-player mode. As expected, there are bots that play alongside the human players. Single-player throws the user into a game with a custom number of bots, and multi-player supports both human and bots to play with each other.

Let's take a look at **multiplayer**. Single-player shares many of the features.

---

### Singup/login
The user must first create an account with which they can log in. With the account, the user can create a unique username for themself and choose a password.

---

### Pre-game lobby and game hosting
Once logged in, the user can either join a game by inputting the game ID into a search bar or create a game as a host.

_Pregame lobby_
<img src="https://i.imgur.com/n15tl0I.png" />

**Admins** have the special ability to remove existing games in the pre-game queue.

_Createding/hosting a game in pregame lobby_
<img src="https://i.imgur.com/OvUFvvJ.png" />

---

### In-game
The player places cards when it is their turn. They can call "cheat" (call a player's bluff when they suspect it). The game ends once a player rids of their deck and cannot gain any more cards.

_In-game_
<img src="https://i.imgur.com/0b3fIdP.png" />

The online portion features **in-game messages** and the ability to play **with real humans _and_ with bots**. Can't get enough friends to play? Just add a bot or two and you're good to go!

### Artificial intelligence
The bots in the game are fully capable of making intelligent moves. They can cheat at appropriate times and call a player's (either human or bot alike) bluff when something seems awry.

### How did it get online?
I used `ngrok` to make a tunnel to my localhost and used my computer as a server.

### So, how do I play?
I'm going to upload a fresh SQL dump with which you can add to phpMyAdmin and play with. You can't play right now on your own computer, yet. :( It's also not online at the moment.
