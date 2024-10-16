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
namespace Sk\SmartId;

use InvalidArgumentException;
use Sk\SmartId\Api\AbstractApi;
use Sk\SmartId\Api\ApiType;
use Sk\SmartId\Api\Authentication;
use Sk\SmartId\Api\Sign;

class Client
{
  const
          DEMO_SID_PUBLIC_KEY_VALID_FROM_2023_09_18_TO_2024_10_14 = "sha256//Ps1Im3KeB0Q4AlR+/J9KFd/MOznaARdwo4gURPCLaVA=",
          DEMO_SID_PUBLIC_KEY_VALID_FROM_2024_10_03_TO_2025_10_15 = "sha256//Ps1Im3KeB0Q4AlR+/J9KFd/MOznaARdwo4gURPCLaVA=",
          RP_API_PUBLIC_KEY_VALID_FROM_2023_09_14_TO_2024_10_15 = "sha256//q/3w4hrhcVsLXeKU6jYGADy2IxVTh9BP1cu+o3isfUA=",
          RP_API_PUBLIC_KEY_VALID_FROM_2024_09_18_TO_2025_10_20 = "sha256//5qbYbM98EtA9yIVCQ1HVnkKyqZBUL6kpHoZfuMN+i8o=",
          VERSION = '5.0';

  /**
   * @var array
   */
  private $apis = array();

  /**
   * @var string
   */
  private $relyingPartyUUID;

  /**
   * @var string
   */
  private $relyingPartyName;

  /**
   * @var string
   */
  private $hostUrl;

    /**
     * @var string
     */
  private $sslKeys;

  /**
   * @param string $apiName
   * @throws InvalidArgumentException
   * @return AbstractApi
   */
  public function api( $apiName )
  {
    switch ( $apiName )
    {
      case ApiType::AUTHENTICATION:
      {
        return $this->authentication();
      }
      case ApiType::SIGN:
      {
        return $this->sign();
      }

      default:
      {
        throw new InvalidArgumentException( 'No such api at present time!' );
      }
    }
  }

  /**
   * @return Authentication
   */
  public function authentication()
  {
    if ( !isset( $this->apis['authentication'] ) )
    {
      $this->apis['authentication'] = new Authentication( $this );
    }

    return $this->apis['authentication'];
  }

  /**
   * @return Sign
   */
  public function sign()
  {
    if ( !isset( $this->apis['sign'] ) )
    {
      $this->apis['sign'] = new Sign( $this );
    }

    return $this->apis['sign'];
  }

  /**
   * @param string $relyingPartyUUID
   * @return $this
   */
  public function setRelyingPartyUUID( $relyingPartyUUID )
  {
    $this->relyingPartyUUID = $relyingPartyUUID;

    return $this;
  }

  /**
   * @return string
   */
  public function getRelyingPartyUUID()
  {
    return $this->relyingPartyUUID;
  }

  /**
   * @param string $relyingPartyName
   * @return $this
   */
  public function setRelyingPartyName( $relyingPartyName )
  {
    $this->relyingPartyName = $relyingPartyName;

    return $this;
  }

  /**
   * @return string
   */
  public function getRelyingPartyName()
  {
    return $this->relyingPartyName;
  }

  /**
   * @param string $hostUrl
   * @return $this
   */
  public function setHostUrl( $hostUrl )
  {
    $this->hostUrl = $hostUrl;

    return $this;
  }

  /**
   * @return string
   */
  public function getHostUrl()
  {
    return $this->hostUrl;
  }

  public function setPublicSslKeys(string $sslKeys)
  {
      $this->sslKeys = $sslKeys;

      return $this;
  }

    public function useOnlyDemoPublicKey()
    {
        $this->sslKeys = sprintf(
            '%s;%s',
            self::DEMO_SID_PUBLIC_KEY_VALID_FROM_2023_09_18_TO_2024_10_14,
            self::DEMO_SID_PUBLIC_KEY_VALID_FROM_2024_10_03_TO_2025_10_15
        );

        return $this;
    }

    public function useOnlyLivePublicKey()
    {
        $this->sslKeys = sprintf(
            '%s;%s',
            self::RP_API_PUBLIC_KEY_VALID_FROM_2023_09_14_TO_2024_10_15,
            self::RP_API_PUBLIC_KEY_VALID_FROM_2024_09_18_TO_2025_10_20
        );

        return $this;
    }

  public function getPublicSslKeys()
  {
      if($this->sslKeys === null)
      {
          $this->sslKeys = sprintf(
              '%s;%s;%s;%s',
              self::DEMO_SID_PUBLIC_KEY_VALID_FROM_2023_09_18_TO_2024_10_14,
              self::DEMO_SID_PUBLIC_KEY_VALID_FROM_2024_10_03_TO_2025_10_15,
              self::RP_API_PUBLIC_KEY_VALID_FROM_2023_09_14_TO_2024_10_15,
              self::RP_API_PUBLIC_KEY_VALID_FROM_2024_09_18_TO_2025_10_20
          );
      }
      return $this->sslKeys;
  }
}
