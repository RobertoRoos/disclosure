# Disclosure

Disclosure is a PHP webapp that allows a user to share secrets with others on the internet, without leaving their secrets exposed or even stored on a server at all, with a limited time-to-live.

## Context

Sharing secrets over the internet is tricky. Sharing e.g. a password in plaintext should never be done, since an account could be compromised or bystanders might simply read the chat. Tools like PGP encryption can help, but require some level of tech-savviness.

One option is to encrypt the secret by some shared knowledge between the recipient and you. For example, you encrypt your secret with the name of your dog and send the ciphertext using chat to your friend, along with the note to use your dog's name to decrypt it. This beats a plaintext communication, but this will leave the ciphertext in your chat log.

## Principle of Disclosure

Disclosure offers the possibility to share password protected content with a set expiration time. It works as follows:

The server keeps a list of expiration tokens, consisting of an identifier, a key and an expiration datetime. These tokens are generated at a regular interval, *independently* from users. Once a token is expired, it is removed from the server.

When you want to create a share, you enter: 

 * Your secret
 * A password
 * A password hint
 * A desired expiration date
 
A link will be produced that includes the following info:

 * The password hint (in plain text)
 * A token identifier (corresponding to an expiration token)
 * A cipher

The cipher is computed as:

```
aes(aes(secret, password), expiration key)
```

The user secret is first encrypted locally using javascript. This first cipher is encrypted again on the server using an expiration key.

When a user wants to read a shared secret, they will need to enter the password (assisted by the password hint). In the meantime the server uses the identifier to find an expiration key. With those combined the secret can be shown.  
In case the token has been expired and was deleted, the link can no longer work.

With this principle, a user password is combined with some general (but varying) server information, such that neither the link itself nor the server itself is enough to retrieve this secret. This means the server does not *need* to be trusted to handle sensitive information, as it never has direct access to begin with.
