import Foundation

public struct APIRequest<Payload: Encodable>: Encodable {
    public let packet: String
    public let host: String
    public let uname: String
    public let psswd: String
    public let table: String
    public let data: Payload

    public init(packet: String, host: String, uname: String, psswd: String, table: String, data: Payload) {
        self.packet = packet
        self.host = host
        self.uname = uname
        self.psswd = psswd
        self.table = table
        self.data = data
    }
}

public struct QueryPayload: Encodable {
    public let query: String
    public init(query: String) { self.query = query }
}

public class SwiftSQL{
    private let url: URL
    private let token: String
    
    public init(endpointURL: URL, bearerToken: String) {
            self.url = endpointURL
            self.token = bearerToken
    }
    
    public func send<Payload: Encodable>(
        packet: String,
        host: String,
        uname: String,
        psswd: String,
        table: String,
        data: Payload,
        completion: @escaping (Result<[String: Any], Error>) -> Void
    ) {
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json; charset=utf-8", forHTTPHeaderField: "Content-Type")
        request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")

        let body = APIRequest(
            packet: packet,
            host: host,
            uname: uname,
            psswd: psswd,
            table: table,
            data: data
        )

        do {
            request.httpBody = try JSONEncoder().encode(body)
        } catch {
            completion(.failure(error))
            return
        }

        URLSession.shared.dataTask(with: request) { data, _, error in
            if let error = error {
                completion(.failure(error))
                return
            }

            guard let data = data else {
                completion(.failure(NSError(domain: "NoData", code: 0)))
                return
            }

            do {
                // Ez itt: a TELJES JSON-t visszaadja [String: Any] formában
                if let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] {
                    completion(.success(json))
                } else {
                    let raw = String(data: data, encoding: .utf8) ?? "?"
                    completion(.failure(NSError(domain: "InvalidJSON", code: 0, userInfo: ["raw": raw])))
                }
            } catch {
                completion(.failure(error))
            }
        }.resume()
    }

}
