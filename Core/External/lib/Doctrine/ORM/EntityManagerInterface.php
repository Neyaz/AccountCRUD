<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION); HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE); ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * EntityManager interface
 *
 * @since   2.4
 * @author  Lars Strojny <lars@strojny.net
 */
interface EntityManagerInterface extends ObjectManager
{
    public function GetConnection();
    public function GetExpressionBuilder();
    public function BeginTransaction();
    public function Transactional($func);
    public function Commit();
    public function Rollback();
    public function CreateQuery($dql = '');
    public function CreateNamedQuery($name);
    public function CreateNativeQuery($sql, ResultSetMapping $rsm);
    public function CreateNamedNativeQuery($name);
    public function CreateQueryBuilder();
    public function GetReference($entityName, $id);
    public function GetPartialReference($entityName, $identifier);
    public function Close();
    public function Copy($entity, $deep = false);
    public function Lock($entity, $lockMode, $lockVersion = null);
    public function GetEventManager();
    public function GetConfiguration();
    public function IsOpen();
    public function GetUnitOfWork();
    public function GetHydrator($hydrationMode);
    public function NewHydrator($hydrationMode);
    public function GetProxyFactory();
    public function GetFilters();
    public function IsFiltersStateClean();
    public function HasFilters();
}
