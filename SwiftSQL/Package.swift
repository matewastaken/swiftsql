
// swift-tools-version: 6.1

import PackageDescription

let package = Package(
    name: "SwiftSQL",
    platforms: [
        .iOS(.v14),
        .macOS(.v11)
    ],
    products: [
        .library(name: "SwiftSQL", targets: ["SwiftSQL"]),
    ],
    targets: [
        .target(name: "SwiftSQL", dependencies: []),
        .testTarget(name: "SwiftSQLTests", dependencies: ["SwiftSQL"]),
    ]
)
