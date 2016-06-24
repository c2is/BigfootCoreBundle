<?php

namespace Bigfoot\Bundle\CoreBundle\Security;

use Google_Client;

/**
 * Gestion des spreadsheets Google Drive
 */
class GoogleDriveOAuth
{

    /** @var string */
    private $clientEmail = null;

    /** @var string */
    private $clientRealEmail = null;

    /** @var string */
    private $scopes = array();

    /** @var string */
    private $privateKey = null;

    /** @var Google_Client */
    private $client = null;


    /**
     * Constructeur
     *
     * @param string $clientEmail
     * @param string $clientRealEmail
     * @param string $privateKey
     */
    public function __construct($clientEmail, $clientRealEmail, $privateKey)
    {
        $this->client = new Google_Client();

        if (null === $clientEmail || null === $clientRealEmail || null === $privateKey) {
            throw new \InvalidArgumentException(sprintf(
                'You must define parameters "%s", "%s" and "%s" in order to use google_drive utilities',
                'bigfoot_core.google_drive.client_email',
                'bigfoot_core.google_drive.client_real_email',
                'bigfoot_core.google_drive.private_key'
            ));
        }
        $this->setClientEmail($clientEmail);
        $this->setClientRealEmail($clientRealEmail);
        $this->setPrivateKey($privateKey);
        $this->addScope('https://spreadsheets.google.com/feeds');
    }

    /**
     * Retourne le client Google
     *
     * @return Google_client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Définit l'email du compte Google
     *
     * @param string $clientEmail
     * @return $this
     */
    public function setClientEmail($email)
    {
        $this->clientEmail = $email;

        return $this;
    }

    /**
     * Retourne l'email du compte Google
     *
     * @return string
     */
    public function getClientEmail()
    {
        return $this->clientEmail;
    }

    /**
     * Définit la véritable adresse email
     *
     * @param string $email
     * @return $this
     */
    public function setClientRealEmail($email)
    {
        $this->clientRealEmail = $email;

        return $this;
    }

    /**
     * Retourne la véritable adresse email
     *
     * @return string
     */
    public function getClientRealEmail()
    {
        return $this->clientRealEmail;
    }

    /**
     * définit le chemin vers le fichier .p12, contenant la clef privée
     *
     * @param string $privateKey
     * @return $this
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * Retourne le chemin vers le fichier .P12, contenant la clef privée
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Ajoute un scope
     *
     * @param string $scope
     * @return $this
     */
    public function addScope($scope)
    {
        $this->scopes[] = $scope;

        return $this;
    }

    /**
     * Retourne les scopes
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Vide tous les scopes
     *
     * @return $this
     */
    public function clearScopes()
    {
        $this->scopes = array();

        return $this;
    }

    /**
     * Authentifie et retourne le token
     *
     * @return string
     */
    public function authenticate()
    {
        if (is_readable($this->getPrivateKey()) === false) {
            throw new \Exception('Le fichier de clef privée "' . $this->getPrivateKey() . '" n\'existe pas ou n\'est pas lisible.');
        }
        $credentials = new \Google_Auth_AssertionCredentials(
            $this->getClientEmail(),
            $this->getScopes(),
            file_get_contents($this->getPrivateKey()),
            'notasecret',
            'http://oauth.net/grant_type/jwt/1.0/bearer',
            $this->getClientRealEmail()
        );

        $this->client->setAssertionCredentials($credentials);
        if ($this->client->getAuth()->isAccessTokenExpired()) {
            $this->client->getAuth()->refreshTokenWithAssertion();
        }

        return $this->getAccessToken();
    }

    /**
     * Retourne toutes les informations du token
     *
     * @return string
     */
    public function getTokenParts()
    {
        return json_decode($this->client->getAuth()->getAccessToken(), true);
    }

    /**
     * Retourne le token d'authentification
     *
     * @return string
     */
    public function getAccessToken()
    {
        $tokenParts = $this->getTokenParts();

        return $tokenParts['access_token'];
    }
}
