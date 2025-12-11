# YAKAN Project Setup Guide

This guide provides automatic setup instructions for the YAKAN mobile application.

## Prerequisites

Before running the setup, ensure you have the following installed:

- **Node.js** (v16 or higher) - [Download](https://nodejs.org/)
- **npm** (comes with Node.js) or **yarn**
- **Expo CLI** - Install globally with: `npm install -g expo-cli`
- **Git** (for version control)

For mobile development:
- **Android Studio** (for Android development)
- **Xcode** (for iOS development on macOS)

## Quick Start Setup

### 1. **Clone the Repository**

```bash
git clone <repository-url>
cd YAKAN-main-main
```

### 2. **Install Dependencies**

```bash
npm install
```

Or if using Yarn:

```bash
yarn install
```

### 3. **Start the Application**

#### For Web:
```bash
npm run web
```

#### For Android:
```bash
npm run android
```

#### For iOS (macOS only):
```bash
npm run ios
```

#### General Expo Start:
```bash
npm start
```

## Project Structure

```
YAKAN-main-main/
├── src/
│   ├── assets/          # Images and media files
│   ├── components/      # Reusable React components
│   ├── config/          # Configuration files
│   ├── constants/       # App constants (colors, tracking, etc.)
│   ├── context/         # React Context for state management
│   ├── screens/         # Screen components
│   └── services/        # API and service files
├── LARAVEL_API_SETUP/   # Backend Laravel setup documentation
├── App.js               # Root app component
├── index.js             # Entry point
├── app.json             # Expo configuration
├── package.json         # Dependencies and scripts
└── setup.md            # This file
```

## Environment Configuration

### Create `.env` file (if needed)

Create a `.env` file in the root directory for API endpoints and configuration:

```env
REACT_APP_API_URL=http://your-api-endpoint.com/api
REACT_APP_API_TIMEOUT=10000
```

Update the `src/config/config.js` file with your environment-specific settings.

## Available Scripts

| Command | Purpose |
|---------|---------|
| `npm start` | Start Expo development server |
| `npm run web` | Run on web browser |
| `npm run android` | Run on Android emulator/device |
| `npm run ios` | Run on iOS simulator/device |

## Backend Setup (Laravel)

If you need to set up the backend API:

1. Navigate to the `LARAVEL_API_SETUP/` folder
2. Follow the instructions in the included PHP files for:
   - AuthController setup
   - Models and Migrations
   - API Routes configuration
   - Payment integration

Refer to `BACKEND_INTEGRATION_SETUP.md` for detailed backend integration steps.

## API Integration

The project uses **Axios** for HTTP requests. Key service files:

- `src/services/api.js` - Main API configuration and base setup
- `src/services/orderService.js` - Order-related API calls
- `useOrders.js` - Custom hook for order management

### API Configuration

Configure your API endpoint in `src/config/config.js`:

```javascript
const API_BASE_URL = 'http://your-api-endpoint.com/api';
```

## State Management

The project uses **React Context** for state management:

- `src/context/CartContext.js` - Shopping cart state and operations

## Troubleshooting

### Common Issues

#### Port Already in Use
If the default Expo port (8081) is in use:
```bash
npm start -- --port 8090
```

#### Dependencies Not Installing
Clear cache and reinstall:
```bash
rm -r node_modules package-lock.json
npm install
```

#### Expo Issues
Update Expo CLI:
```bash
npm install -g expo-cli@latest
```

#### Module Not Found
Clear Expo cache:
```bash
expo start -c
```

## Development Workflow

1. **Make changes** to your code in the `src/` directory
2. **Save files** - The app will hot-reload automatically
3. **Test on device** - Use the Expo Go app or emulator
4. **Commit changes** using Git

## Build for Production

### Web Build
```bash
expo export --platform web
```

### Android Build
```bash
expo build:android
```

### iOS Build
```bash
expo build:ios
```

## Additional Resources

- [Expo Documentation](https://docs.expo.dev/)
- [React Native Documentation](https://reactnative.dev/)
- [Axios Documentation](https://axios-http.com/)
- [React Navigation Documentation](https://reactnavigation.org/)

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review `INTEGRATION_GUIDE.md` for integration details
3. Check `API_IMPLEMENTATION_EXAMPLES.md` for API usage examples
4. Consult the backend setup guides in `LARAVEL_API_SETUP/`

## License

[Specify your license here]

---

**Last Updated**: December 2025
**Version**: 1.0.0
