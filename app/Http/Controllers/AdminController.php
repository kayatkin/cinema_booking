<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\SeancesMovie;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except(['showLoginForm', 'login']); // Защита всех методов, кроме логина
    }

    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            Log::info('Администратор вошел в систему: ' . $request->input('email'));
            $request->session()->regenerate();
            return redirect()->route('admin.index');
        }

        return back()->withErrors([
            'email' => 'Неверные учетные данные',
        ]);
    }

    public function logout(Request $request)
{
    if (Auth::guard('admin')->check()) {
        Log::info('Администратор вышел: ' . Auth::guard('admin')->user()->email);
        Auth::guard('admin')->logout();
    }

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login');
}

public function index()
{
    try {
        // Получаем все доступные залы с их сеансами и фильмами
        $halls = Hall::with('seancesMovies.movie')->get();

        // Получаем все фильмы
        $movies = Movie::all();

        // Получаем все сеансы с их фильмами и залами, сортируем по времени
        $seancesMovies = SeancesMovie::with('movie', 'hall')->orderBy('start_time')->get();

        // Проверяем, есть ли вообще залы
        $hallsExist = $halls->isNotEmpty();

        // Проверяем, активен ли хотя бы один зал (если есть залы)
        $isActive = $hallsExist ? $halls->first()->is_active : false;

        // Возвращаем представление с данными
        return view('admin.index', compact('halls', 'movies', 'seancesMovies', 'isActive', 'hallsExist'));

    } catch (\Exception $e) {
        // Логируем ошибку
        Log::error('Ошибка при загрузке страницы admin.index: ' . $e->getMessage());

        // Перенаправляем на страницу авторизации с сообщением об ошибке
        return redirect()->route('admin.login')->withErrors(['message' => 'Произошла ошибка при загрузке данных.']);
    }
}

    /**
     * Сохраням новый зал в базу данных (упрощенное создание, только название).
     */
    public function storeSimpleHall(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255', // Ограничение на длину названия
        ]);

        // Создаем зал с минимальными данными
        $hall = Hall::create([
            'name' => $validatedData['name'],
            'rows' => 1, // По умолчанию 1 ряд
            'seats_per_row' => 1, // По умолчанию 1 место в ряду
        ]);

        // Возвращаем JSON-ответ для AJAX-запроса
        return response()->json([
            'success' => true,
            'message' => 'Зал успешно создан!',
            'hall' => $hall, // Возвращаем данные созданного зала
        ], 200);
    }

    /**
     * Удаляем зал из базы данных.
     */
    public function deleteHall(Request $request, $hall_id)
    {
        // Находим зал по ID
        $hall = Hall::findOrFail($hall_id);

        // Проверяем, что зал не используется в сеансах
        if ($hall->seancesMovies()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Зал нельзя удалить, так как он используется в сеансах.',
            ], 400);
        }

        // Удаляем связанные конфигурации и цены
        $hall->configurations()->delete();
        $hall->pricings()->delete();

        // Удаляем сам зал
        $hall->delete();

        return response()->json([
            'success' => true,
            'message' => 'Зал успешно удален!',
        ], 200);
    }

    /**
     * Сохраням новую конфигурацию зала.
     */
    public function saveHallConfiguration(Request $request, $hall_id)
    {
        // Валидация входных данных
        $validatedData = $request->validate([
            'rows' => 'required|integer|min:1', // Количество рядов
            'seats_per_row' => 'required|integer|min:1', // Количество мест в ряду
            'configuration' => 'required|array', // Конфигурация мест
            'configuration.*.global_seat' => 'integer|min:1', // Номер места
            'configuration.*.type' => 'string|in:standart,vip,disabled', // Тип места
        ]);

        // Находим зал
        $hall = Hall::findOrFail($hall_id);

        // Обновляем количество рядов и мест в ряду
        $hall->update([
            'rows' => $validatedData['rows'],
            'seats_per_row' => $validatedData['seats_per_row'],
        ]);

        // Удаляем старую конфигурацию
        $hall->configurations()->delete();

        // Сохраняем новую конфигурацию
        foreach ($validatedData['configuration'] as $seat) {
            $hall->configurations()->create([
                'global_seat_number' => $seat['global_seat'],
                'seat_type' => $seat['type'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Конфигурация зала успешно сохранена!',
        ], 200);
    }

    /**
     * Возвращаем текущую конфигурацию зала.
     */
    public function getHallConfiguration($hall_id)
    {
        // Находим зал с его конфигурацией
        $hall = Hall::with('configurations')->findOrFail($hall_id);

        // Формируем конфигурацию мест
        $configuration = $hall->configurations->map(function ($seat) {
            return [
                'global_seat' => $seat->global_seat_number,
                'type' => $seat->seat_type,
            ];
        })->toArray();

        // Возвращаем данные
        return response()->json([
            'success' => true,
            'rows' => $hall->rows, // Количество рядов
            'seats_per_row' => $hall->seats_per_row, // Количество мест в ряду
            'configuration' => $configuration, // Конфигурация мест
        ], 200);
    }

    /**
     * Возвращаем текущие цены для зала.
     */
    public function getHallPricing($hall_id)
    {
        $hall = Hall::with('pricings')->findOrFail($hall_id);

        $pricing = [
            'standart_price' => $hall->pricings()->where('seat_type', 'standart')->value('price') ?? 0,
            'vip_price' => $hall->pricings()->where('seat_type', 'vip')->value('price') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'pricing' => $pricing,
        ], 200);
    }

    /**
     * Сохраняем новые цены для зала.
     */
    public function saveHallPricing(Request $request, $hall_id)
    {
        $validatedData = $request->validate([
            'standart_price' => 'required|numeric|min:0',
            'vip_price' => 'required|numeric|min:0',
        ]);

        $hall = Hall::findOrFail($hall_id);

        // Удаляем старые цены
        $hall->pricings()->delete();

        // Сохраняем новые цены
        $hall->pricings()->create([
            'seat_type' => 'standart',
            'price' => $validatedData['standart_price'],
        ]);

        $hall->pricings()->create([
            'seat_type' => 'vip',
            'price' => $validatedData['vip_price'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Цены успешно установлены!',
        ], 200);
    }

    /**
     * Создает новый сеанс через AJAX.
     */
    public function createSeance(Request $request)
    {
        try {
            // Валидация входных данных
            $validatedData = $request->validate([
                'movie_id' => 'required|exists:movies,id',
                'hall_id' => 'required|exists:halls,id',
                'start_time' => 'required|date_format:H:i',
            ]);

            // Получаем продолжительность фильма
            $movie = Movie::findOrFail($validatedData['movie_id']);
            $duration = $movie->duration; // Продолжительность фильма в минутах

            // Преобразуем start_time в объект Carbon (с текущей датой)
            $startTime = Carbon::today()->setTimeFromTimeString($validatedData['start_time']);
            $endTime = $startTime->copy()->addMinutes($duration);

            // Проверяем пересечение с существующими сеансами
            $overlappingSeances = SeancesMovie::where('hall_id', $validatedData['hall_id'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->exists();

            if ($overlappingSeances) {
                return response()->json([
                    'success' => false,
                    'message' => 'Время сеанса пересекается с существующими сеансами.',
                ], 400);
            }

            // Создаем новый сеанс
            $seance = SeancesMovie::create([
                'movie_id' => $validatedData['movie_id'],
                'hall_id' => $validatedData['hall_id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Сеанс успешно добавлен!',
                'seance' => [
                    'id' => $seance->id,
                    'movie_id' => $seance->movie_id,
                    'hall_id' => $seance->hall_id,
                    'start_time' => $seance->start_time->format('H:i'),
                    'end_time' => $seance->end_time->format('H:i'),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error("Ошибка при создании сеанса: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка при создании сеанса.',
            ], 500);
        }
    }

    /**
     * Сохраняем список сеансов.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'seances' => 'required|array',
            'seances.*.hall_id' => 'required|integer|exists:halls,id',
            'seances.*.movie_id' => 'required|integer|exists:movies,id',
            'seances.*.start_time' => 'required|string',
            'seances.*.end_time' => 'required|string',
        ]);

        foreach ($data['seances'] as $seanceData) {
            SeancesMovie::create([
                'movie_id' => $seanceData['movie_id'],
                'hall_id' => $seanceData['hall_id'],
                'start_time' => Carbon::parse($seanceData['start_time']),
                'end_time' => Carbon::parse($seanceData['end_time']),
            ]);
        }

        return response()->json(['message' => 'Сеансы успешно сохранены!']);
    }

    /**
     * Загружаем список всех сеансов.
     */
    public function loadSeances()
    {
        $seances = SeancesMovie::with('movie')->get()->map(function ($seance) {
            $startTime = Carbon::parse($seance->start_time);
            $endTime = $startTime->copy()->addMinutes($seance->movie->duration);

            return [
                'id' => $seance->id,
                'hall_id' => $seance->hall_id,
                'movie_id' => $seance->movie_id,
                'movie_title' => $seance->movie->title,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
            ];
        });

        return response()->json($seances);
    }
    public function deleteSeance(Request $request, $id)
    {
        try {
            // Находим сеанс по ID
            $seance = SeancesMovie::findOrFail($id);

            // Удаляем сеанс
            $seance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Сеанс успешно удален!',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Ошибка при удалении сеанса: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка при удалении сеанса.',
            ], 500);
        }
    }
    /**
     * Создаем новый фильм.
     */
    public function createMovie(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255', // Название фильма
            'duration' => 'required|integer|min:1', // Продолжительность в минутах
            'synopsis' => 'nullable|string', // Описание фильма (опционально)
            'origin' => 'nullable|string', // Страна производства (опционально)
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Постер (опционально)
        ]);

        // Если загружен постер, сохраняем его
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('posters', 'public');
            $validatedData['poster_path'] = $posterPath;
        } else {
            $validatedData['poster_path'] = null; // Устанавливаем значение null, если постера нет
        }

        // Создаем новый фильм
        $movie = Movie::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Фильм успешно создан!',
            'movie' => $movie, // Возвращаем данные созданного фильма
        ], 200);
    }

    /**
     * Возвращаем данные о фильме для редактирования.
     */
    public function getMovieForEdit($movie_id)
    {
        $movie = Movie::findOrFail($movie_id);

        return response()->json([
            'success' => true,
            'movie' => [
                'id' => $movie->id,
                'title' => $movie->title,
                'duration' => $movie->duration,
                'synopsis' => $movie->synopsis,
                'origin' => $movie->origin,
                'poster_path' => $movie->poster_path,
            ],
        ], 200);
    }
    /**
     * Обновляем данные о фильме.
     */
    public function updateMovie(Request $request, $movie_id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'synopsis' => 'nullable|string',
            'origin' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $movie = Movie::findOrFail($movie_id);

        // Проверяем, загружен ли новый постер
        if ($request->hasFile('poster')) {
            // Удаляем старый постер, если он есть
            if ($movie->poster_path) {
                Storage::disk('public')->delete($movie->poster_path);
            }
            // Сохраняем новый постер
            $posterPath = $request->file('poster')->store('posters', 'public');
            $validatedData['poster_path'] = $posterPath;
        } else {
            // Оставляем старый путь к постеру
            $validatedData['poster_path'] = $movie->poster_path;
        }

        // Обновляем данные фильма
        $movie->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Фильм успешно обновлен!',
            'movie' => $movie,
        ], 200);
    }

    /**
     * Удаляем фильм из базы данных.
     */
    public function deleteMovie($movie_id)
    {
        try {
            $movie = Movie::findOrFail($movie_id);

            // Проверяем, используется ли фильм в сеансах
            if ($movie->seances()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Фильм нельзя удалить, так как он используется в сеансах.',
                ], 400);
            }

            // Удаляем постер, если он есть
            if ($movie->poster_path) {
                Storage::disk('public')->delete($movie->poster_path);
            }

            // Удаляем фильм
            $movie->delete();

            return response()->json([
                'success' => true,
                'message' => 'Фильм успешно удален!',
            ], 200);
        } catch (\Exception $e) {
            // Лог ошибки для отладки
            Log::error("Ошибка при удалении фильма ID {$movie_id}: " . $e->getMessage());

            // Сообщение об ошибке
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка при удалении фильма.',
            ], 500);
        }
    }
    public function toggleSales(Request $request)
    {
        // Получаем текущий статус всех залов
        $currentStatus = Hall::first()->is_active ?? false;

        // Инвертируем статус для всех залов
        Hall::query()->update(['is_active' => !$currentStatus]);

        // Возвращаем новый статус и текст кнопки
        return response()->json([
            'success' => true,
            'is_active' => !$currentStatus,
            'button_text' => !$currentStatus ? 'Приостановить продажу билетов' : 'Открыть продажу билетов',
        ]);
    }
}
