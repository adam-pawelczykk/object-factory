
## 🚀 Uruchomienie projektu 

```bash
docker compose build
docker-compose run --rm app bash
```

## Przykładowa fabryka encji Customera
```
namespace Tests\ObjectFactory\Customer;

use ATPawelczyk\ObjectFactory\Interfaces\FactoryDefinitionInterface;
use ATPawelczyk\ObjectFactory\ObjectFactory;
use Sample\Customer; 

class CustomerFactory implements FactoryDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public function defaultProperties(Generator $faker, object $object): array
    {
        return [
            'name' => $faker->firstName,
            'surname' => $faker->lastName
        ];
    }

    /**
     * @inheritDoc
     */
    public static function definitionClass(): string
    {
        return Customer::class;
    }
}
```

## Dopięcie ObjectManagera do testów
```
$this->objectFactory = new ObjectFactory('Tests\ObjectFactory');
```

## Użycie
```
class CustomerTest extends ApiTestCase 
{
    public function testShouldCreateCustomerEntity(): void
    {
        /** @var Customer $customer */
        $customer = $this->objectFactory->create(Customer::class);
        // Boom entity was created...
    }
}
```
