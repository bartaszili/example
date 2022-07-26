<?php

declare(strict_types=1);

namespace Logger\Entity;

use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/**
 * Doctrine ORM specific entity implementation.
 * Getters and setters.
 * Logic is designed to handle create and read requests.\
 * **Please do not update logs!**\
 * *If so, with this logic the created date will be ower-written.*
 *
 * @ORM\Entity
 * @ORM\Table(name="logs")
 **/
class Logs {
    /**
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /** @ORM\Column(type="json_array", nullable=true) */
    private $message;

    /** @ORM\Column(type="text", nullable=true) */
    private $url;

    /** @ORM\Column(nullable=true) */
    private $logger;

    /** @ORM\Column(type="datetime") */
    private $created;

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getId(): ?string { return $this->id; }
    // setId by Doctrine ORM

    private function getMessage(): ?array { return $this->message; }
    private function setMessage(array $input): void { $this->message = $input; }

    private function getUrl(): ?string { return $this->url; }
    private function setUrl(?string $input): void { $this->url = $input; }

    private function getLogger(): ?string { return $this->logger; }
    private function setLogger(string $input): void { $this->logger = $input; }

    private function getCreated(): ?DateTime { return $this->created; }
    private function setCreated(DateTime $input = null): void { if(empty($input) && empty($this->getId())) { $this->created = new DateTime("now"); } else { $this->created = $input; } }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function getLog(): array {
        return [
            'id'      => $this->getId(),
            'message' => $this->getMessage(),
            'url'     => $this->getUrl(),
            'logger'  => $this->getLogger(),
            'created' => $this->getCreated()
        ];
    }

    public function setLog(array $input): void {
        if (isset($input['message']) && !empty($input['message'])) { $this->setMessage($input['message']); }
        if (isset($input['url'])     && !empty($input['url']))     { $this->setUrl($input['url']); }
        if (isset($input['logger'])  && !empty($input['logger']))  { $this->setLogger($input['logger']); }
        $this->setCreated();
    }
}
