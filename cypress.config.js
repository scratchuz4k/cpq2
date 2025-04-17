import { defineConfig } from "cypress";
import fs from "fs";

export default defineConfig({
    chromeWebSecurity: false,
    defaultCommandTimeout: 5000,
    watchForFileChanges: false,
    videoCompression: true,
    videosFolder: "cypress/output/videos",
    screenshotsFolder: "cypress/output/screenshots",
    video: true, // Optionally, enable videos for failed tests
    screenshotOnRunFailure: true, // Ensure screenshots are captured on test failure
    fixturesFolder: "cypress/fixture",
    e2e: {
        baseUrl: "http://localhost:8000",
        specPattern: "cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",
        supportFile: "cypress/support/index.js",
        setupNodeEvents(on, config) {
            on("after:spec", (spec, results) => {
                if (results && results.video) {
                    // Do we have failures for any retry attempts?
                    const failures = results.tests.some((test) =>
                        test.attempts.some(
                            (attempt) => attempt.state === "failed"
                        )
                    );
                    if (!failures) {
                        // delete the video if the spec passed and no tests retried
                        fs.unlinkSync(results.video);
                    }
                }
            });
        },
    },
});
