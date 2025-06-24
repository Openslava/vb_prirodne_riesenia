<?php

/**
 * List of payments methods. Types of payment methods is a fixed list. Methods can be allowed or disabled.
 * User: kuba
 * Date: 05.04.16
 * Time: 12:41
 */

/**
 * Enumeration of all payment methods supported by shop.
 * @class MwsPayType
 */
class MwsPayType extends MwsBasicEnum {
  const CreditCard = 'creditCard';
  const WireOnline = 'wireOnline';
  const Sms = 'sms';
  const PayPal = 'paypal';
//  const Cash = 'cash';
  const Cod = 'cod';
  const Wire = 'wire';

  protected static function doInitCaptions() {
    return array(
      self::Wire => __('Bankovní převod (1-2 dny)', 'mwshop'),
      self::CreditCard => __('Online platební karta (ihned)', 'mwshop'),
      self::WireOnline => __('Online bankovní převod (ihned)', 'mwshop'),
      self::Sms => __('SMS (m-platba) (ihned)', 'mwshop'),
      self::PayPal => __('PayPal (ihned)', 'mwshop'),
//      self::Cash => __('Hotovost', 'mwshop'),
      self::Cod => __('Při převzetí', 'mwshop'),
    );
  }

  public static function getDescriptions() {
    return array(
      self::Wire => __('Běžný bankovní převod je známý a oblíbený převod peněz mezi dvěma bankovními ústavy. Platbu lze zadat kdykoliv, ale zpracována je v úředních hodinách vaší banky. Zadejte platební příkaz ve vaší bance.', 'mwshop'),
      self::CreditCard => __('Platba platební kartou přes internet patří mezi dnes nejrozšířenější platební metody. Platební karty jsou chytře zabezpečené, rychlé a každý má v peněžence alespoň jednu. Informace o zaplacení se k obchodníkovi dostane ihned. Budete přesměrováni na platební bránu.', 'mwshop'),
      self::WireOnline => __('Jedná se o oblíbenou bankovní platbu na jedno kliknutí s předvyplněným platebním příkazem přímo z vašeho internetového bankovnictví a okamžitým převodem peněz na účet obchodníka. Budete přesměrováni na platební bránu.', 'mwshop'),
      self::Sms => __('m-platba je platební metoda, která umožňuje zadat příkaz k převodu peněz a zaplatit prostřednictvím mobilního telefonu. Tuto službu musíte mít povolenou u vašeho mobilního operátora. S pomocí mPlatby lze zaplatit částky až do výše 1500 Kč. Budete přesměrováni na platební bránu.', 'mwshop'),
      self::PayPal => __('Systém PayPal je elektronický internetový platební prostředek a nejrozšířenějším celosvětově používaným systémem pro online platby. Účet v systému PayPal funguje podobně jako běžný bankovní účet a přesun peněz z účtu kupujícího na účet prodávajícího probíhá okamžitě jako kdyby měli stejnou banku – PayPal. Budete přesměrováni na platební bránu.', 'mwshop'),
//      self::Cash => __('Platba v hotovosti předpokládá váš osobní kontakt s obchodníkem. K zaplacení se osobně spojte s obchodníkem.', 'mwshop'),
      self::Cod => __('Platba při převzetí je platební metoda, kdy se vybírá částka za produkt až při převzetí produktu od přepravce nebo v prodejně. Platba od vás bude vyžadována při převzetí zboží.', 'mwshop'),
    );
  }

}
