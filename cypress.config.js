import { defineConfig } from 'cypress'

export default defineConfig({
    retries: 3,
    chromeWebSecurity: false,
    defaultCommandTimeout: 5000,
    watchForFileChanges: false,
    videoCompression: true,
    videosFolder: 'cypress/output/videos',
    screenshotsFolder: 'cypress/output/screenshots',
    video: true,  // Optionally, enable videos for failed tests
    screenshotOnRunFailure: true,  // Ensure screenshots are captured on test failure
    fixturesFolder: 'cypress/fixture',
    e2e: {
        baseUrl: 'http://localhost:8000',
        specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
        supportFile: 'cypress/support/index.js',
    },
})
