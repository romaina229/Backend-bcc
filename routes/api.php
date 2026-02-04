<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\ModuleController;
use App\Http\Controllers\API\LessonController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\ProgressController;
use App\Http\Controllers\API\ForumController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\CertificateController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes - CORRECTED
|--------------------------------------------------------------------------
*/

// Routes publiques - Authentification
Route::post('/connexion', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login']); // Alias pour compatibilité
Route::post('/inscription', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'register']); // Alias
Route::post('/mot-de-passe-oublie', [AuthController::class, 'forgotPassword']);
Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'resetPassword']);

// Cours publics
Route::get('/cours', [CourseController::class, 'indexPublic']);
Route::get('/cours/{id}', [CourseController::class, 'showPublic']);

// Routes protégées par authentification
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentification
    Route::post('/deconnexion', [AuthController::class, 'logout']);
    Route::post('/logout', [AuthController::class, 'logout']); // Alias
    Route::get('/utilisateur', [AuthController::class, 'user']);
    Route::get('/me', [AuthController::class, 'user']); // Alias
    Route::put('/profil', [UserController::class, 'updateProfile']);
    Route::put('/mot-de-passe', [UserController::class, 'updatePassword']);
    Route::post('/avatar', [UserController::class, 'uploadAvatar']);
    
    // Tableaux de bord
    Route::get('/dashboard/etudiant', [DashboardController::class, 'studentDashboard']);
    Route::get('/dashboard/formateur', [DashboardController::class, 'instructorDashboard']);
    Route::get('/dashboard/admin', [DashboardController::class, 'adminDashboard']);
    
    // Cours et formations
    Route::get('/mes-cours', [CourseController::class, 'myCourses']);
    Route::post('/cours/{id}/inscription', [EnrollmentController::class, 'enroll']);
    Route::get('/cours/{id}/contenu', [CourseController::class, 'content']);
    Route::get('/cours/{id}/progression', [ProgressController::class, 'courseProgress']);
    
    // Modules et leçons
    Route::get('/modules/{id}', [ModuleController::class, 'show']);
    Route::get('/lecons/{id}', [LessonController::class, 'show']);
    Route::post('/lecons/{id}/completer', [LessonController::class, 'complete']);
    
    // Quiz et évaluations
    Route::get('/quiz/semaine/{week}', [QuizController::class, 'getWeeklyQuiz']);
    Route::get('/quiz/{id}', [QuizController::class, 'show']);
    Route::post('/quiz/{id}/soumettre', [QuizController::class, 'submitQuiz']);
    Route::get('/quiz/{id}/resultats', [QuizController::class, 'getResults']);
    Route::get('/quiz/historique', [QuizController::class, 'quizHistory']);
    
    // Forum de discussion
    Route::get('/forum/categories', [ForumController::class, 'categories']);
    Route::get('/forum/categorie/{id}/discussions', [ForumController::class, 'categoryDiscussions']);
    Route::get('/forum/discussions', [ForumController::class, 'discussions']);
    Route::post('/forum/discussions', [ForumController::class, 'createDiscussion']);
    Route::get('/forum/discussions/{id}', [ForumController::class, 'showDiscussion']);
    Route::put('/forum/discussions/{id}', [ForumController::class, 'updateDiscussion']);
    Route::delete('/forum/discussions/{id}', [ForumController::class, 'deleteDiscussion']);
    
    // Posts et commentaires
    Route::post('/forum/discussions/{id}/posts', [PostController::class, 'store']);
    Route::put('/forum/posts/{id}', [PostController::class, 'update']);
    Route::delete('/forum/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/forum/posts/{id}/commentaires', [CommentController::class, 'store']);
    Route::put('/forum/commentaires/{id}', [CommentController::class, 'update']);
    Route::delete('/forum/commentaires/{id}', [CommentController::class, 'destroy']);
    
    // Paiements
    Route::post('/paiement/initier', [PaymentController::class, 'initiatePayment']);
    Route::post('/paiement/verifier', [PaymentController::class, 'verifyPayment']);
    Route::get('/paiements/historique', [PaymentController::class, 'paymentHistory']);
    
    // Certificats
    Route::get('/certificats', [CertificateController::class, 'index']);
    Route::get('/certificats/{id}/telecharger', [CertificateController::class, 'download']);
    Route::get('/certificats/{id}/verifier', [CertificateController::class, 'verify']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/lire', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/tout-lire', [NotificationController::class, 'markAllAsRead']);
    
    // Routes administrateur
    Route::middleware(['can:admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/utilisateurs', [AdminController::class, 'indexUsers']);
            Route::put('/utilisateurs/{id}/statut', [AdminController::class, 'updateUserStatus']);
            Route::delete('/utilisateurs/{id}', [AdminController::class, 'deleteUser']);
            
            Route::get('/cours', [AdminController::class, 'indexCourses']);
            Route::put('/cours/{id}/statut', [AdminController::class, 'updateCourseStatus']);
            Route::delete('/cours/{id}', [AdminController::class, 'deleteCourse']);
            
            Route::apiResource('cours', CourseController::class)->except(['index', 'show']);
            Route::post('/cours/{id}/modules', [ModuleController::class, 'store']);
            Route::post('/modules/{id}/lecons', [LessonController::class, 'store']);
            
            Route::get('/stats', [DashboardController::class, 'advancedStats']);
            Route::get('/statistiques', [DashboardController::class, 'statistics']);
            Route::get('/rapports', [DashboardController::class, 'reports']);
            
            Route::get('/export/utilisateurs', [AdminController::class, 'exportUsers']);
            Route::get('/export/cours', [AdminController::class, 'exportCourses']);
            Route::get('/export/paiements', [AdminController::class, 'exportPayments']);
        });
    });
    
    // Routes formateur
    Route::middleware(['can:instructor'])->group(function () {
        Route::prefix('formateur')->group(function () {
            Route::get('/cours', [CourseController::class, 'instructorCourses']);
            Route::get('/cours/{id}/etudiants', [EnrollmentController::class, 'courseStudents']);
            Route::post('/quiz', [QuizController::class, 'store']);
            Route::get('/quiz/{id}/reponses', [QuizController::class, 'getQuizResponses']);
            Route::post('/cours/{id}/certificats', [CertificateController::class, 'generateForCourse']);
        });
    });
});

// Routes de test
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});