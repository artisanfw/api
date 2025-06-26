<?php

namespace Artisan\Routing\Entities;

use Spatie\ArrayToXml\ArrayToXml;


class XmlApiResponse extends ApiResponse
{
    public function getContentType(): string
    {
        return 'application/xml';
    }

    protected function beforeSend(): void
    {
        if (empty($this->getPayload())) return;

        if ($this->getPayload() instanceof \SimpleXMLElement) {
            $payload = $this->getPayload()->asXML();
        }
        elseif ($this->getPayload() instanceof \DOMDocument) {
            $this->getPayload()->formatOutput = true;
            $payload = $this->getPayload()->saveXML();
        }
        elseif (is_array($this->getPayload())) {
            $payload = ArrayToXml::convert($this->getPayload(), '', true, $this->getCharset());
        }
        else {
            $payload = (string) $this->getPayload();
        }

        if (!str_starts_with(ltrim($payload), '<?xml ')) {
            $payload = '<?xml version="1.0" encoding="'.$this->getCharset().'"?>' . $payload;
        }

        $this->setPayload($payload);
    }
}