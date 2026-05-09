# Flutter Barcode Scanner + Laravel API

A learning guide for building a barcode scanner app with Flutter that communicates with a Laravel API backend.

## Why Flutter?

- Cross-platform (iOS & Android from one codebase)
- Good match for existing skillset
- Excellent documentation and community support
- Strong performance for camera operations

## Core Skills to Master

### 1. State Management
- **Start with:** `setState()` for simple apps
- **Graduate to:** Provider or Riverpod (lighter than BLoC)

### 2. Camera & Barcode Scanning
- **Package:** `mobile_scanner` (easiest, well-maintained)
- Real-time frame processing is straightforward

### 3. HTTP & API Integration
- **Package:** `dio` (better than http for interceptors, retries, error handling)
- Use `json_serializable` for request/response models

### 4. Async Patterns
- Master `Future` and `async/await`
- Understand how widgets rebuild when async operations complete

## Project Structure

```
lib/
  models/         # API request/response models
  services/       # API client, barcode scanner wrapper
  screens/        # UI screens
  providers/      # State management
```

## Quick Example

```dart
// Simple example: barcode scan → send to Laravel API
final result = await scanner.scan();
final response = await dio.post(
  'https://your-laravel-api.com/api/barcodes',
  data: {'barcode': result.rawValue},
);
```

## Learning Resources

- [Flutter Official Docs](https://flutter.dev/docs) — excellent, comprehensive
- `mobile_scanner` pub.dev page — great examples
- YouTube: Traversy Media Flutter tutorials — solid beginner content

## Laravel Backend

Keep it simple:
- Expose a `POST /api/barcodes` endpoint
- Accept barcode data in request body
- Return JSON response

The API integration from Flutter is straightforward once you understand `dio` and async/await.

## Next Steps

1. Set up Flutter development environment
2. Create a new Flutter project
3. Add `mobile_scanner` and `dio` packages
4. Start with camera preview + simple API call
5. Build out state management as complexity grows
