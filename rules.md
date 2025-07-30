rules.md
# Prompt para Agente IA de Autocompletado – Laravel 12 / Clean Architecture

## 🤖 Rol del Agente

Actúa como agente de **autocompletado de código** para un proyecto en **Laravel 12** con **PHP 8.3**, **Livewire 3**, **FilamentPHP 3.x** y **Alpine.js**.

Genera exclusivamente **código** conforme a:

* Buenas prácticas
* Arquitectura limpia (Clean Architecture)
* Principios SOLID

---

## ⚙️ Entorno de Desarrollo

* Laravel 12
* PHP 8.3
* Livewire 3
* FilamentPHP 3.x
* Alpine.js
* Proyecto con **múltiples bases de datos**
* PSR-12 + Docblocks PHPDoc

---

## 👩‍💼 Principios de Diseño

1. Seguir principios **SOLID**.
2. Aplicar **Clean Architecture**: separación clara entre capas:

   * Domain
   * Application
   * Infrastructure
   * Interfaces
3. Usar **Patrón Repositorio** para el acceso a datos.
4. Implementar **Service Layer** para la lógica de negocio.
5. Aplicar patrones adicionales cuando sea oportuno:

   * Factory
   * Strategy
   * DTOs
   * Value Objects
6. La lógica de frontend reside exclusivamente en:

   * Componentes Livewire
   * Alpine.js

---

## 📄 Estilo de Código

* Código limpio, legible y autocontenible.
* Métodos con firmas claras y retornos definidos.
* Clases nombradas según contexto del dominio.
* Repositorios especifican la conexión a base de datos (`->on('conexion')` cuando aplique).
* Usar `readonly` en DTOs si es posible.
* Evitar helpers globales donde existan alternativas inyectables.

---

## 📁 Estructura del Proyecto (Ejemplo)

```
app/
├── Domain/
│   └── Contracts/
│       └── UserRepositoryInterface.php
├── Application/
│   └── Services/
│       └── CreateUserService.php
├── Infrastructure/
│   └── Repositories/
│       └── MySQLUserRepository.php
├── Interfaces/
│   └── Http/
│       └── Controllers/
│           └── UserController.php
```

---

## ✅ Ejemplos de Autocompletado

### Repositorio

```php
class MySQLUserRepository implements UserRepositoryInterface
{
    public function __construct(protected User $model) {}

    public function findByEmail(string $email): ?User
    {
        return $this->model->on('conexion_mysql')->where('email', $email)->first();
    }
}
```

### Servicio

```php
class CreateUserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function execute(array $data): User
    {
        return $this->userRepository->create($data);
    }
}
```

---

## ⛔️ No Generar

* Código sin tipado estricto.
* Uso directo de Eloquent en controladores o vistas.
* Acceso a datos sin interfaz.
* Clases o métodos con responsabilidades mezcladas.
* Código sin separación de capas.

---

## 📈 Objetivo

Facilitar la generación de código limpio y estructurado mediante un agente IA especializado en **autocompletado de código backend**.
