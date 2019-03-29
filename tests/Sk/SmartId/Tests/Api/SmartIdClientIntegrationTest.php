<?php
/*-
 * #%L
 * Smart ID sample PHP client
 * %%
 * Copyright (C) 2018 - 2019 SK ID Solutions AS
 * %%
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * #L%
 */
namespace Sk\SmartId\Tests\Api;

use Sk\SmartId\Api\AuthenticationResponseValidator;
use Sk\SmartId\Api\Data\AuthenticationHash;
use Sk\SmartId\Api\Data\AuthenticationIdentity;
use Sk\SmartId\Api\Data\SmartIdAuthenticationResponse;
use Sk\SmartId\Api\Data\SmartIdAuthenticationResult;
use Sk\SmartId\Tests\Setup;

class SmartIdClientIntegrationTest extends Setup
{
  /**
   * @after
   */
  public function waitForMobileAppToFinish()
  {
    sleep( 10 );
  }

  /**
   * @test
   * @throws \ReflectionException
   */
  public function authenticate_withDocumentNumber()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $authenticationResponse = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS[ 'relying_party_uuid' ] )
        ->withRelyingPartyName( $GLOBALS[ 'relying_party_name' ] )
        ->withDocumentNumber( $GLOBALS[ 'document_number' ] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel( $GLOBALS[ 'certificate_level' ] )
        ->authenticate();

    $this->assertAuthenticationResponseCreated( $authenticationResponse, $authenticationHash->getDataToSign() );

    $authenticationResponseValidator = new AuthenticationResponseValidator( self::RESOURCES );
    $authenticationResult = $authenticationResponseValidator->validate( $authenticationResponse );
    $this->assertAuthenticationResultValid( $authenticationResult );
  }

  /**
   * @test
   * @throws \ReflectionException
   */
  public function authenticate_withNationalIdentityNumberAndCountryCode()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $authenticationResponse = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS[ 'relying_party_uuid' ] )
        ->withRelyingPartyName( $GLOBALS[ 'relying_party_name' ] )
        ->withNationalIdentityNumber( $GLOBALS[ 'national_identity_number' ] )
        ->withCountryCode( $GLOBALS[ 'country_code' ] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel( $GLOBALS[ 'certificate_level' ] )
        ->authenticate();

    $this->assertAuthenticationResponseCreated( $authenticationResponse, $authenticationHash->getDataToSign() );

    $authenticationResponseValidator = new AuthenticationResponseValidator( self::RESOURCES );
    $authenticationResult = $authenticationResponseValidator->validate( $authenticationResponse );
    $this->assertAuthenticationResultValid( $authenticationResult );
  }

  /**
   * @test
   */
  public function startAuthenticationAndReturnSessionId_withNationalIdentityNumberAndCountryCode()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $sessionId = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS[ 'relying_party_uuid' ] )
        ->withRelyingPartyName( $GLOBALS[ 'relying_party_name' ] )
        ->withNationalIdentityNumber( $GLOBALS[ 'national_identity_number' ] )
        ->withCountryCode( $GLOBALS[ 'country_code' ] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel( $GLOBALS[ 'certificate_level' ] )
        ->startAuthenticationAndReturnSessionId();

    $this->assertNotEmpty( $sessionId );
  }

  /**
   * @test
   */
  public function getAuthenticationResponse_withSessionId_isRunning()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $sessionId = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS[ 'relying_party_uuid' ] )
        ->withRelyingPartyName( $GLOBALS[ 'relying_party_name' ] )
        ->withNationalIdentityNumber( $GLOBALS[ 'national_identity_number' ] )
        ->withCountryCode( $GLOBALS[ 'country_code' ] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel( $GLOBALS[ 'certificate_level' ] )
        ->withDisplayText( 'DO NOT ENTER CODE' )
        ->startAuthenticationAndReturnSessionId();

    $this->assertNotEmpty( $sessionId );

    $authenticationResponse = $this->client->authentication()
        ->setSessionStatusResponseSocketTimeoutMs( 1000 )
        ->createSessionStatusFetcher()
        ->withSessionId( $sessionId )
        ->withAuthenticationHash( $authenticationHash )
        ->getAuthenticationResponse();

    $this->assertNotNull( $authenticationResponse );
    $this->assertTrue( $authenticationResponse->isRunningState() );
  }

  /**
   * @test
   * @throws \ReflectionException
   */
  public function getAuthenticationResponse_withSessionId_isComplete()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $sessionId = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS[ 'relying_party_uuid' ] )
        ->withRelyingPartyName( $GLOBALS[ 'relying_party_name' ] )
        ->withNationalIdentityNumber( $GLOBALS[ 'national_identity_number' ] )
        ->withCountryCode( $GLOBALS[ 'country_code' ] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel( $GLOBALS[ 'certificate_level' ] )
        ->startAuthenticationAndReturnSessionId();

    $this->assertNotEmpty( $sessionId );

    // Polling logic exposed
    $authenticationResponse = null;

    for ( $i = 0; $i <= 10; $i++ )
    {
      $authenticationResponse = $this->client->authentication()
          ->createSessionStatusFetcher()
          ->withSessionId( $sessionId )
          ->withAuthenticationHash( $authenticationHash )
          ->withSessionStatusResponseSocketTimeoutMs( 10000 )
          ->getAuthenticationResponse();

      $this->assertNotNull( $authenticationResponse );

      if ( !$authenticationResponse->isRunningState() )
      {
        break;
      }
      sleep( 5 );
    }

    $this->assertNotNull( $authenticationResponse );
    $this->assertFalse( $authenticationResponse->isRunningState() );

    $this->assertAuthenticationResponseCreated( $authenticationResponse, $authenticationHash->getDataToSign() );

    $authenticationResponseValidator = new AuthenticationResponseValidator( self::RESOURCES );
    $authenticationResult = $authenticationResponseValidator->validate( $authenticationResponse );
    $this->assertAuthenticationResultValid( $authenticationResult );
  }

  /**
   * @test
   * @throws \ReflectionException
   */
  public function authenticate_withNetworkInterfaceInPlace()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $authenticationResponse = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS[ 'relying_party_uuid' ] )
        ->withRelyingPartyName( $GLOBALS[ 'relying_party_name' ] )
        ->withNetworkInterface( 'eth0' ) // or available IP
        ->withDocumentNumber( $GLOBALS[ 'document_number' ] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel( $GLOBALS[ 'certificate_level' ] )
        ->authenticate();

    $this->assertAuthenticationResponseCreated( $authenticationResponse, $authenticationHash->getDataToSign() );

    $authenticationResponseValidator = new AuthenticationResponseValidator( self::RESOURCES );
    $authenticationResult = $authenticationResponseValidator->validate( $authenticationResponse );
    $this->assertAuthenticationResultValid( $authenticationResult );
  }

  /**
   * @test
   */
  public function getAuthenticationResponse_withNetworkInterfaceInPlaceAndSessionId_isRunning()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $sessionId = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS[ 'relying_party_uuid' ] )
        ->withRelyingPartyName( $GLOBALS[ 'relying_party_name' ] )
        ->withNetworkInterface( 'eth0' ) // or available IP
        ->withNationalIdentityNumber( $GLOBALS[ 'national_identity_number' ] )
        ->withCountryCode( $GLOBALS[ 'country_code' ] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel( $GLOBALS[ 'certificate_level' ] )
        ->withDisplayText( 'DO NOT ENTER CODE' )
        ->startAuthenticationAndReturnSessionId();

    $this->assertNotEmpty( $sessionId );

    $authenticationResponse = $this->client->authentication()
        ->setSessionStatusResponseSocketTimeoutMs( 1000 )
        ->createSessionStatusFetcher()
        ->withNetworkInterface( 'eth0' ) // or available IP
        ->withSessionId( $sessionId )
        ->withAuthenticationHash( $authenticationHash )
        ->getAuthenticationResponse();

    $this->assertNotNull( $authenticationResponse );
    $this->assertTrue( $authenticationResponse->isRunningState() );
  }

  /**
   * @param SmartIdAuthenticationResponse $authenticationResponse
   * @param string $dataToSign
   */
  private function assertAuthenticationResponseCreated( SmartIdAuthenticationResponse $authenticationResponse,
                                                        $dataToSign )
  {
    $this->assertNotNull( $authenticationResponse );
    $this->assertNotEmpty( $authenticationResponse->getEndResult() );
    $this->assertEquals( $dataToSign, $authenticationResponse->getSignedData() );
    $this->assertNotEmpty( $authenticationResponse->getValueInBase64() );
    $this->assertNotNull( $authenticationResponse->getCertificate() );
    $this->assertNotNull( $authenticationResponse->getCertificateInstance() );
    $this->assertNotNull( $authenticationResponse->getCertificateLevel() );
  }

  /**
   * @param SmartIdAuthenticationResult $authenticationResult
   */
  private function assertAuthenticationResultValid( SmartIdAuthenticationResult $authenticationResult )
  {
    $this->assertTrue( $authenticationResult->isValid() );
    $this->assertTrue( empty( $authenticationResult->getErrors() ) );
    $this->assertAuthenticationIdentityValid( $authenticationResult->getAuthenticationIdentity() );
  }

  /**
   * @param AuthenticationIdentity $authenticationIdentity
   */
  private function assertAuthenticationIdentityValid( AuthenticationIdentity $authenticationIdentity )
  {
    $this->assertNotEmpty( $authenticationIdentity->getGivenName() );
    $this->assertNotEmpty( $authenticationIdentity->getSurName() );
    $this->assertNotEmpty( $authenticationIdentity->getIdentityCode() );
    $this->assertNotEmpty( $authenticationIdentity->getCountry() );
  }
}
