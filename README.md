# Twoje Mieszkanie API

API do zarządzania nieruchomościami i najmem mieszkaniowym. Aplikacja umożliwia właścicielom zarządzanie obiektami, przypisywanie najemców, śledzenie płatności czynszu oraz komunikację między użytkownikami.

## 🚀 Funkcjonalności

- **Autentykacja użytkowników** - rejestracja, logowanie, wylogowanie, zmiana hasła
- **Zarządzanie obiektami** - CRUD dla nieruchomości (domy, mieszkania, pokoje)
- **Przypisania najmu** - zarządzanie umowami najmu między właścicielami a najemcami
- **Zarządzanie czynszem** - tworzenie, akceptacja i potwierdzanie płatności czynszu
- **System wiadomości** - komunikacja między użytkownikami
- **Dokumentacja API** - automatyczna dokumentacja Swagger/OpenAPI

## 📋 Wymagania

- PHP >= 8.2
- Composer
- Node.js >= 18.x i npm
- SQLite (lub MySQL/PostgreSQL)

## 🔧 Instalacja

1. **Sklonuj repozytorium**
   ```bash
   git clone <repository-url>
   cd twoje-mieszkanie-api
   ```

2. **Zainstaluj zależności PHP**
   ```bash
   composer install
   ```

3. **Skonfiguruj środowisko**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Skonfiguruj bazę danych**

   Edytuj plik `.env` i ustaw konfigurację bazy danych:
   ```env
   DB_CONNECTION=sqlite
   # lub dla MySQL/PostgreSQL:
   # DB_CONNECTION=mysql
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=twoje_mieszkanie
   # DB_USERNAME=root
   # DB_PASSWORD=
   ```

5. **Uruchom migracje**
   ```bash
   php artisan migrate
   ```

6. **Zainstaluj zależności frontendowe**
   ```bash
   npm install
   ```

7. **Zbuduj zasoby frontendowe** (opcjonalnie)
   ```bash
   npm run build
   ```

## 🚀 Uruchomienie

### Szybka instalacja (wszystkie kroki naraz)
```bash
composer run setup
```

### Tryb deweloperski
```bash
composer run dev
```

To uruchomi jednocześnie:
- Serwer Laravel (`php artisan serve`)
- Kolejkę zadań (`php artisan queue:listen`)
- Logi w czasie rzeczywistym (`php artisan pail`)
- Serwer Vite (`npm run dev`)

### Ręczne uruchomienie

**Serwer API:**
```bash
php artisan serve
```

API będzie dostępne pod adresem: `http://localhost:8000`

**Serwer Vite (dla frontendu):**
```bash
npm run dev
```

## 📚 Dokumentacja API

Po uruchomieniu aplikacji, dokumentacja Swagger jest dostępna pod adresem:

```
http://localhost:8000/api/documentation
```

Aby wygenerować dokumentację API:
```bash
php artisan l5-swagger:generate
```

## 🔐 Autentykacja

API używa Laravel Sanctum do autentykacji. Większość endpointów wymaga tokenu autoryzacyjnego.

### Rejestracja
```http
POST /api/register
Content-Type: application/json

{
  "name": "Jan Kowalski",
  "email": "jan@example.com",
  "password": "haslo123",
  "password_confirmation": "haslo123"
}
```

### Logowanie
```http
POST /api/login
Content-Type: application/json

{
  "email": "jan@example.com",
  "password": "haslo123"
}
```

Odpowiedź zawiera token, który należy używać w nagłówku:
```http
Authorization: Bearer {token}
```

## 📡 Endpointy API

### Autentykacja
- `POST /api/register` - Rejestracja użytkownika
- `POST /api/login` - Logowanie
- `POST /api/logout` - Wylogowanie (wymaga autoryzacji)
- `POST /api/changePassword` - Zmiana hasła (wymaga autoryzacji)
- `GET /api/user` - Informacje o zalogowanym użytkowniku (wymaga autoryzacji)

### Obiekty
- `GET /api/object` - Lista obiektów użytkownika
- `GET /api/object/{id}` - Szczegóły obiektu
- `POST /api/object` - Utworzenie obiektu
- `PUT /api/object/{id}` - Aktualizacja obiektu
- `DELETE /api/object/{id}` - Usunięcie obiektu

### Przypisania najmu
- `GET /api/rentAssigment` - Lista przypisań najmu
- `GET /api/rentAssigment/{id}` - Szczegóły przypisania
- `POST /api/rentAssigment` - Utworzenie przypisania najmu
- `DELETE /api/rentAssigment/{id}` - Usunięcie przypisania

### Czynsz
- `GET /api/fullRent/{id}` - Szczegóły czynszu
- `POST /api/fullRent` - Utworzenie czynszu
- `PUT /api/fullRent/{id}` - Aktualizacja czynszu
- `PUT /api/fullRent/accept/{id}` - Akceptacja czynszu
- `PUT /api/fullRent/confirmPaid/{id}` - Potwierdzenie płatności
- `DELETE /api/fullRent/{id}` - Usunięcie czynszu

### Wiadomości
- `GET /api/messages` - Lista konwersacji
- `GET /api/messages/{partner}` - Wiadomości z konkretnym użytkownikiem
- `POST /api/messages/{receiver}` - Wysłanie wiadomości

## 🧪 Testy

Uruchom testy:
```bash
composer run test
```

lub bezpośrednio:
```bash
php artisan test
```

## 🛠️ Narzędzia deweloperskie

### Formatowanie kodu
```bash
./vendor/bin/pint
```

### Tinker (interaktywna konsola Laravel)
```bash
php artisan tinker
```

## 📁 Struktura projektu

```
twoje-mieszkanie-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Kontrolery API
│   │   └── Requests/        # Form requesty
│   ├── Models/              # Modele Eloquent
│   ├── Rules/               # Własne reguły walidacji
│   └── Services/            # Logika biznesowa
├── database/
│   ├── migrations/          # Migracje bazy danych
│   └── seeders/            # Seedery
├── routes/
│   └── api.php              # Definicje tras API
├── config/                  # Pliki konfiguracyjne
└── storage/
    └── api-docs/            # Wygenerowana dokumentacja Swagger
```

## 🔒 Bezpieczeństwo

- Wszystkie hasła są hashowane przy użyciu bcrypt
- API używa Laravel Sanctum do autoryzacji tokenowej
- Wszystkie endpointy (oprócz rejestracji i logowania) wymagają autoryzacji
- Walidacja danych wejściowych przez Form Requests

## 📝 Licencja

Projekt jest otwartym oprogramowaniem dostępnym na licencji [MIT](https://opensource.org/licenses/MIT).

## 🤝 Wsparcie

W przypadku pytań lub problemów, utwórz issue w repozytorium projektu.
